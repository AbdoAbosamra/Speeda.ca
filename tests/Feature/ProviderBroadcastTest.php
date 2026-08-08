<?php

namespace Tests\Feature;

use App\Jobs\SendProviderBroadcastEmail;
use App\Mail\Provider\BroadcastEmail;
use App\Models\Category;
use App\Models\Location;
use App\Models\ProviderBroadcast;
use App\Models\ProviderBroadcastRecipient;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Support\AdminHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * End-to-end cover for the admin "Email All Providers" feature: composing,
 * previewing, the guarded send, delivery bookkeeping, and unsubscribing.
 */
class ProviderBroadcastTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function makeProvider(array $userAttributes = [], array $providerAttributes = []): ServiceProvider
    {
        $location = Location::factory()->create(['city' => 'BroadcastCity_' . uniqid()]);
        $category = Category::factory()->create();

        $provider = ServiceProvider::factory()
            ->forLocation($location->id)
            ->forCategory($category->id)
            ->create($providerAttributes + ['is_active' => true]);

        $provider->user->update($userAttributes + ['is_active' => true]);

        return $provider->fresh('user');
    }

    private function draft(array $attributes = []): ProviderBroadcast
    {
        return ProviderBroadcast::create($attributes + [
            'subject' => 'Big news for {{ provider_name }}',
            'body' => '<p>Hello {{ provider_name }}, we shipped something.</p>',
            'status' => ProviderBroadcast::STATUS_DRAFT,
            'created_by' => $this->admin->id,
        ]);
    }

    /* ================================================================== */
    /*  Access control                                                     */
    /* ================================================================== */

    public function test_non_admin_cannot_reach_the_broadcast_screens(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);

        $this->actingAs($client)
            ->get(route('admin.broadcasts.index'))
            ->assertForbidden();
    }

    public function test_guest_cannot_reach_the_broadcast_screens(): void
    {
        $this->get(route('admin.broadcasts.index'))->assertRedirect(route('login'));
    }

    /* ================================================================== */
    /*  Authoring                                                          */
    /* ================================================================== */

    public function test_admin_can_open_the_index_and_compose_screens(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.broadcasts.index'))->assertOk()->assertSee('Email All Providers');
        $this->get(route('admin.broadcasts.create'))->assertOk()->assertSee('Compose Email');
    }

    /**
     * The compose screen's front-end is wired together by name/id agreement
     * between separate elements. A rename on one side alone silently breaks the
     * editor or the preview with no server-side error, so the contract is
     * asserted here rather than left to manual clicking.
     */
    public function test_the_compose_screen_ships_its_editor_and_preview_wiring(): void
    {
        $this->actingAs($this->admin);

        $html = $this->get(route('admin.broadcasts.create'))->assertOk()->getContent();

        // Rich editor: the same TinyMCE setup the blog CMS uses.
        $this->assertStringContainsString('tinymce', $html);
        $this->assertStringContainsString('class="rich-editor"', $html);

        // Preview: the hidden form must target a browsing context that the
        // iframe actually declares in the markup.
        $this->assertStringContainsString('target="broadcast-preview-frame-target"', $html);
        $this->assertStringContainsString('name="broadcast-preview-frame-target"', $html);
        $this->assertStringContainsString(route('admin.broadcasts.preview'), $html);

        // Every field the preview endpoint validates must exist in the form.
        foreach (['subject', 'preheader', 'body', 'cta_label', 'cta_url'] as $field) {
            $this->assertStringContainsString('name="' . $field . '"', $html);
        }
    }

    public function test_the_send_controls_appear_only_on_a_saved_draft(): void
    {
        $this->actingAs($this->admin);

        // Nothing to send before the draft exists.
        $this->get(route('admin.broadcasts.create'))
            ->assertOk()
            ->assertDontSee('Type SEND to confirm', false);

        $broadcast = $this->draft();

        $this->get(route('admin.broadcasts.edit', $broadcast))
            ->assertOk()
            ->assertSee('Send Test')
            ->assertSee(route('admin.broadcasts.send', $broadcast), false);
    }

    public function test_the_feature_is_reachable_from_the_admin_navigation(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.broadcasts.index'), false)
            ->assertSee('Email All Providers');
    }

    public function test_admin_can_save_a_draft(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.broadcasts.store'), [
            'subject' => 'Welcome to the new gallery',
            'preheader' => 'Show your best work',
            'body' => '<p>We just launched galleries.</p>',
            'cta_label' => 'Open Dashboard',
            'cta_url' => 'https://speeda.ca/dashboard',
        ]);

        $broadcast = ProviderBroadcast::first();

        $this->assertNotNull($broadcast);
        $response->assertRedirect(route('admin.broadcasts.edit', $broadcast));
        $this->assertSame(ProviderBroadcast::STATUS_DRAFT, $broadcast->status);
        $this->assertSame($this->admin->id, $broadcast->created_by);
    }

    public function test_a_button_label_without_a_link_is_rejected(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.broadcasts.store'), [
            'subject' => 'Subject',
            'body' => '<p>Body</p>',
            'cta_label' => 'Click me',
        ])->assertSessionHasErrors('cta_url');
    }

    public function test_a_body_that_is_only_markup_is_rejected(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.broadcasts.store'), [
            'subject' => 'Subject',
            'body' => '<p></p><div>   </div>',
        ])->assertSessionHasErrors('body');
    }

    /* ================================================================== */
    /*  Sanitising                                                         */
    /* ================================================================== */

    public function test_active_content_is_stripped_before_the_body_is_stored(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.broadcasts.store'), [
            'subject' => 'Hello',
            'body' => '<p onclick="steal()">Real copy</p>'
                . '<script>alert(1)</script>'
                . '<iframe src="https://evil.test"></iframe>'
                . '<a href="javascript:alert(2)">bad link</a>',
        ]);

        $body = ProviderBroadcast::first()->body;

        $this->assertStringContainsString('Real copy', $body);
        $this->assertStringNotContainsString('<script', $body);
        $this->assertStringNotContainsString('<iframe', $body);
        $this->assertStringNotContainsString('onclick', $body);
        $this->assertStringNotContainsString('javascript:', $body);
    }

    public function test_the_sanitiser_keeps_ordinary_formatting(): void
    {
        $clean = AdminHtml::clean('<p><strong>Bold</strong> and <a href="https://speeda.ca">a link</a></p><ul><li>item</li></ul>');

        $this->assertStringContainsString('<strong>Bold</strong>', $clean);
        $this->assertStringContainsString('href="https://speeda.ca"', $clean);
        $this->assertStringContainsString('<li>item</li>', $clean);
    }

    /* ================================================================== */
    /*  Preview                                                            */
    /* ================================================================== */

    public function test_preview_renders_the_real_email_with_placeholders_filled(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.broadcasts.preview'), [
            'subject' => 'Hi {{ provider_name }}',
            'body' => '<p>Hello {{ provider_name }}, welcome to {{ site_name }}.</p>',
        ]);

        $response->assertOk();
        $response->assertSee($this->admin->name, false);
        $response->assertDontSee('{{ provider_name }}', false);
    }

    /* ================================================================== */
    /*  Sending                                                            */
    /* ================================================================== */

    public function test_sending_requires_the_typed_confirmation(): void
    {
        $this->actingAs($this->admin);
        $this->makeProvider();
        $broadcast = $this->draft();

        $this->post(route('admin.broadcasts.send', $broadcast), ['confirm' => 'yes'])
            ->assertSessionHasErrors('confirm');

        $this->assertSame(ProviderBroadcast::STATUS_DRAFT, $broadcast->fresh()->status);
        $this->assertSame(0, ProviderBroadcastRecipient::count());
    }

    public function test_sending_queues_one_job_per_eligible_provider(): void
    {
        Queue::fake();
        $this->actingAs($this->admin);

        $this->makeProvider();
        $this->makeProvider();

        // Excluded: inactive provider, disabled user, and an unsubscriber.
        $this->makeProvider(providerAttributes: ['is_active' => false]);
        $this->makeProvider(userAttributes: ['is_active' => false]);
        $this->makeProvider(userAttributes: ['broadcast_opt_out_at' => now()]);

        $broadcast = $this->draft();

        $this->post(route('admin.broadcasts.send', $broadcast), ['confirm' => 'SEND'])
            ->assertRedirect(route('admin.broadcasts.show', $broadcast));

        $broadcast->refresh();

        $this->assertSame(2, ProviderBroadcastRecipient::count());
        $this->assertSame(2, $broadcast->recipients_total);
        $this->assertSame(ProviderBroadcast::STATUS_QUEUED, $broadcast->status);
        Queue::assertPushed(SendProviderBroadcastEmail::class, 2);
    }

    public function test_a_second_send_cannot_mail_the_same_broadcast_twice(): void
    {
        Queue::fake();
        $this->actingAs($this->admin);
        $this->makeProvider();

        $broadcast = $this->draft();
        $this->post(route('admin.broadcasts.send', $broadcast), ['confirm' => 'SEND']);

        $this->post(route('admin.broadcasts.send', $broadcast), ['confirm' => 'SEND'])
            ->assertRedirect(route('admin.broadcasts.show', $broadcast));

        $this->assertSame(1, ProviderBroadcastRecipient::count());
        Queue::assertPushed(SendProviderBroadcastEmail::class, 1);
    }

    public function test_a_queued_broadcast_can_no_longer_be_edited_or_deleted(): void
    {
        Queue::fake();
        $this->actingAs($this->admin);
        $this->makeProvider();

        $broadcast = $this->draft();
        $this->post(route('admin.broadcasts.send', $broadcast), ['confirm' => 'SEND']);

        $this->get(route('admin.broadcasts.edit', $broadcast))
            ->assertRedirect(route('admin.broadcasts.show', $broadcast));

        $this->put(route('admin.broadcasts.update', $broadcast), [
            'subject' => 'Rewritten history',
            'body' => '<p>Different message</p>',
        ])->assertRedirect(route('admin.broadcasts.show', $broadcast));

        $this->delete(route('admin.broadcasts.destroy', $broadcast));

        $this->assertSame('Big news for {{ provider_name }}', $broadcast->fresh()->subject);
        $this->assertDatabaseHas('provider_broadcasts', ['id' => $broadcast->id]);
    }

    public function test_the_job_delivers_the_email_and_records_the_result(): void
    {
        Mail::fake();
        $this->actingAs($this->admin);

        $provider = $this->makeProvider();
        $broadcast = $this->draft();

        // Queue not faked: jobs run inline on the sync connection used in tests.
        $this->post(route('admin.broadcasts.send', $broadcast), ['confirm' => 'SEND']);

        Mail::assertSent(BroadcastEmail::class, 1);
        Mail::assertSent(BroadcastEmail::class, fn ($mail) => $mail->hasTo($provider->user->email));

        $broadcast->refresh();
        $this->assertSame(1, $broadcast->sent_count);
        $this->assertSame(0, $broadcast->failed_count);
        $this->assertSame(ProviderBroadcast::STATUS_SENT, $broadcast->status);
        $this->assertNotNull($broadcast->sent_at);

        $this->assertSame(
            ProviderBroadcastRecipient::STATUS_SENT,
            ProviderBroadcastRecipient::first()->status
        );
    }

    public function test_someone_who_unsubscribes_mid_send_is_not_mailed(): void
    {
        Mail::fake();
        $this->actingAs($this->admin);

        $provider = $this->makeProvider();
        $broadcast = $this->draft();

        $recipient = ProviderBroadcastRecipient::create([
            'provider_broadcast_id' => $broadcast->id,
            'service_provider_id' => $provider->id,
            'email' => $provider->user->email,
            'name' => 'Test Provider',
            'status' => ProviderBroadcastRecipient::STATUS_PENDING,
        ]);

        $provider->user->update(['broadcast_opt_out_at' => now()]);

        (new SendProviderBroadcastEmail($recipient->id))
            ->handle(app(\App\Services\ProviderBroadcastService::class));

        Mail::assertNothingSent();
        $this->assertSame(ProviderBroadcastRecipient::STATUS_FAILED, $recipient->fresh()->status);
    }

    public function test_the_delivery_report_and_progress_endpoint_are_available(): void
    {
        Mail::fake();
        $this->actingAs($this->admin);
        $this->makeProvider();

        $broadcast = $this->draft();
        $this->post(route('admin.broadcasts.send', $broadcast), ['confirm' => 'SEND']);

        $this->get(route('admin.broadcasts.show', $broadcast))->assertOk();

        $this->getJson(route('admin.broadcasts.progress', $broadcast))
            ->assertOk()
            ->assertJson(['finished' => true, 'sent' => 1, 'percent' => 100]);
    }

    /* ================================================================== */
    /*  Unsubscribe                                                        */
    /* ================================================================== */

    public function test_an_unsigned_unsubscribe_link_is_rejected(): void
    {
        $provider = $this->makeProvider();

        $this->get('/email/unsubscribe/' . $provider->user->id)->assertForbidden();
    }

    public function test_a_signed_link_lets_a_provider_unsubscribe_without_logging_in(): void
    {
        $provider = $this->makeProvider();
        $user = $provider->user;

        $showUrl = URL::signedRoute('broadcast.unsubscribe', ['user' => $user->id]);
        $this->get($showUrl)->assertOk()->assertSee('Unsubscribe from Speeda emails');

        $confirmUrl = URL::signedRoute('broadcast.unsubscribe.confirm', ['user' => $user->id]);
        $this->post($confirmUrl)->assertOk();

        $this->assertNotNull($user->fresh()->broadcast_opt_out_at);
    }

    public function test_an_unsubscribed_provider_can_resubscribe(): void
    {
        $provider = $this->makeProvider(userAttributes: ['broadcast_opt_out_at' => now()]);
        $user = $provider->user;

        $this->post(URL::signedRoute('broadcast.resubscribe', ['user' => $user->id]))->assertOk();

        $this->assertNull($user->fresh()->broadcast_opt_out_at);
    }

    public function test_the_email_carries_a_working_unsubscribe_link(): void
    {
        Mail::fake();
        $this->actingAs($this->admin);

        $provider = $this->makeProvider();
        $broadcast = $this->draft();

        $this->post(route('admin.broadcasts.send', $broadcast), ['confirm' => 'SEND']);

        Mail::assertSent(BroadcastEmail::class, function (BroadcastEmail $mail) {
            $html = $mail->render();

            return str_contains($html, '/email/unsubscribe/')
                && str_contains($html, 'signature=');
        });
    }
}
