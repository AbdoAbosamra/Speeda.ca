<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Category;
use App\Models\Comment;
use App\Models\EmailTemplate;
use App\Models\Location;
use App\Models\Post;
use App\Models\ProviderEmailLog;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\SiteTestimonial;
use App\Models\User;
use App\Services\ProviderEmailJourneyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * TEMPORARY smoke test verifying the new (last-5-days) features end-to-end.
 */
class NewAdminFeaturesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'client_welcome_email_sent_at' => now()]);
        $this->actingAs($this->admin);
    }

    private function makeProvider(): ServiceProvider
    {
        $loc = Location::factory()->create(['city' => 'SmokeCity_' . uniqid()]);
        $cat = Category::factory()->create();

        return ServiceProvider::factory()->forLocation($loc->id)->forCategory($cat->id)->create();
    }

    private function makeClient(): User
    {
        return User::factory()->create(['role' => 'client']);
    }

    /* ------------------------------------------------------------------ */
    /*  1. All new admin pages render 200                                  */
    /* ------------------------------------------------------------------ */

    public function test_all_new_admin_pages_render(): void
    {
        $provider = $this->makeProvider();
        $provider->user->update(['role' => 'service_provider']);

        $comment = Comment::create([
            'commentable_type' => ServiceProvider::class,
            'commentable_id' => $provider->id,
            'user_id' => $this->makeClient()->id,
            'content' => 'test comment',
            'is_active' => false,
        ]);

        $notification = AdminNotification::factory()->create();

        $post = Post::factory()->create();

        foreach ([
            route('admin.login_activity.index'),
            route('admin.email_journey.index'),
            route('admin.email_templates.index'),
            route('admin.email_templates.edit', 'welcome'),
            route('admin.email_templates.preview', 'welcome'),
            route('admin.testimonials.index'),
            route('admin.providers.edit', $provider->id),
            route('admin.comments.show', $comment->id),
            route('admin.notifications.show', $notification->id),
            route('admin.blog.posts.trash'),
            route('admin.blog.posts.edit', $post),
        ] as $url) {
            $response = $this->get($url);
            if ($response->status() !== 200) {
                fwrite(STDERR, "\nNON-200: $url => " . $response->status() . "\n");
            }
            $response->assertStatus(200);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  2. Email templates: index/edit/preview/save/reset + validation     */
    /* ------------------------------------------------------------------ */

    public function test_email_template_index_lists_all_defaults(): void
    {
        $this->get(route('admin.email_templates.index'))
            ->assertOk()
            ->assertSee('welcome')
            ->assertSee('client_welcome');
    }

    public function test_email_template_save_override_then_reset(): void
    {
        Mail::fake();

        $this->put(route('admin.email_templates.update', 'photo'), [
            'subject' => 'Custom subject {{ provider_name }}',
            'headline' => 'Custom headline',
            'is_active' => 1,
        ])->assertRedirect(route('admin.email_templates.edit', 'photo'));

        $this->assertDatabaseHas('email_templates', ['key' => 'photo', 'is_active' => true]);

        $resolved = EmailTemplate::resolve('photo', ['provider_name' => 'ACME']);
        $this->assertSame('Custom subject ACME', $resolved['subject']);
        $this->assertSame('Custom headline', $resolved['headline']);

        // Cheap woof check for the badge default staying else
        $this->assertSame('Step 1 of 6', $resolved['badge']);

        // Reset restores the built-in default
        $this->delete(route('admin.email_templates.reset', 'photo'))
            ->assertRedirect(route('admin.email_templates.edit', 'photo'));

        $this->assertDatabaseMissing('email_templates', ['key' => 'photo']);
        $resolved = EmailTemplate::resolve('photo');
        $this->assertSame('📸 One Photo Can Change Everything – Add Yours Today', $resolved['subject']);
    }

    public function test_email_template_rejects_unknown_placeholder(): void
    {
        $this->put(route('admin.email_templates.update', 'welcome'), [
            'subject' => 'Hello {{ providername }}',
            'headline' => 'ok',
        ])->assertSessionHasErrors('subject');
    }

    public function test_all_email_previews_render(): void
    {
        $provider = $this->makeProvider();
        $provider->user->update(['role' => 'service_provider', 'is_active' => true]);

        foreach (['welcome', 'photo', 'services', 'bio', 'experience', 'gallery', 'service_areas', 'complete', 'reviews'] as $type) {
            $this->get(route('admin.email_journey.preview', $type))->assertOk();
        }
    }

    /* ------------------------------------------------------------------ */
    /*  3. Email journey index/show + safe send-test guard                 */
    /* ------------------------------------------------------------------ */

    public function test_email_journey_pages_render(): void
    {
        $provider = $this->makeProvider();

        $this->get(route('admin.email_journey.index'))->assertOk();
        $this->get(route('admin.email_journey.show', $provider->id))->assertOk();
    }

    public function test_email_journey_send_test_aborts_on_type_mismatch(): void
    {
        Mail::fake();

        $provider = $this->makeProvider();

        $this->post(route('admin.email_journey.send_test', $provider->id), [
            'expected_type' => 'reviews', // certainly not the actual next email
        ])->assertRedirect(route('admin.email_journey.show', $provider->id))
          ->assertSessionHas('warning');

        $this->assertSame(0, ProviderEmailLog::count(), 'No email may be logged when the expected type mismatches.');
    }

    public function test_email_journey_dry_run_and_stats(): void
    {
        $provider = $this->makeProvider();
        $svc = app(ProviderEmailJourneyService::class);

        $sent = $svc->processProvider($provider, dryRun: true);
        // Dry run never touches provider_email_logs, even when an email is due.
        $this->assertSame(0, ProviderEmailLog::count(), 'Dry run must not persist a log row.');
        if ($sent !== null) {
            $this->assertContains($sent, ['photo', 'services', 'bio', 'experience', 'gallery', 'service_areas', 'complete', 'reviews']);
        }

        $stats = $svc->getAdminStats();
        $this->assertArrayHasKey('total_providers', $stats);
        $this->assertArrayHasKey('total_sent', $stats);
    }

    /* ------------------------------------------------------------------ */
    /*  4. Testimonials: CRUD, toggle, bulk, home display                  */
    /* ------------------------------------------------------------------ */

    public function test_testimonial_crud_toggle_and_home_display(): void
    {
        $provider = $this->makeProvider();

        // Store 3 active testimonials
        for ($i = 1; $i <= 3; $i++) {
            $this->post(route('admin.testimonials.store'), [
                'service_provider_id' => $provider->id,
                'rating' => 5,
                'testimonial_text' => "Great service $i",
                'is_active' => 1,
                'sort_order' => $i,
            ])->assertRedirect(route('admin.testimonials.index'));
        }

        $this->assertSame(3, SiteTestimonial::count());

        // Home section appears with exactly 3 active
        $home = $this->get(route('home'))->assertOk();
        $home->assertSee('Great service 1');

        // Toggle one off -> home section hides
        $this->patch(route('admin.testimonials.toggle', SiteTestimonial::first()->id))
            ->assertRedirect(route('admin.testimonials.index'));
        $this->get(route('home'))->assertDontSee('Great service 1');

        // Update
        $t = SiteTestimonial::first();
        $this->patch(route('admin.testimonials.update', $t->id), [
            'service_provider_id' => $provider->id,
            'rating' => 4,
            'testimonial_text' => 'Updated text',
            'is_active' => 1,
        ])->assertRedirect(route('admin.testimonials.index'));

        // Delete
        $this->delete(route('admin.testimonials.destroy', $t->id))
            ->assertRedirect(route('admin.testimonials.index'));
        $this->assertDatabaseMissing('site_testimonials', ['id' => $t->id]);
    }

    public function test_testimonials_bulk_deactivate(): void
    {
        $provider = $this->makeProvider();
        $ids = [];
        foreach ([1, 2, 3] as $i) {
            $ids[] = SiteTestimonial::create([
                'service_provider_id' => $provider->id,
                'provider_name' => $provider->company_name,
                'rating' => 5,
                'testimonial_text' => "bulk $i",
                'is_active' => true,
                'sort_order' => $i,
            ])->id;
        }

        $this->post(route('admin.testimonials.bulk'), ['bulk_action' => 'deactivate', 'ids' => $ids])
            ->assertRedirect();

        $this->assertSame(0, SiteTestimonial::where('is_active', true)->count());
    }

    /* ------------------------------------------------------------------ */
    /*  5. Provider management: edit/update/toggles/bulk/destroy            */
    /* ------------------------------------------------------------------ */

    public function test_provider_management_toggles_and_bulk(): void
    {
        $provider = $this->makeProvider();

        $this->get(route('admin.providers.edit', $provider->id))->assertOk();

        $this->patch(route('admin.providers.update', $provider->id), [
            'company_name' => 'Renamed Co',
            'is_active' => 1,
            'is_verified' => 1,
            'is_featured' => 1,
        ])->assertRedirect(route('admin.providers.edit', $provider->id));

        $this->assertDatabaseHas('service_providers', ['id' => $provider->id, 'company_name' => 'Renamed Co', 'is_active' => 1]);

        // Toggles flip the flags
        $this->patch(route('admin.providers.toggle_active', $provider->id))->assertRedirect();
        $this->assertDatabaseHas('service_providers', ['id' => $provider->id, 'is_active' => 0]);
        $this->patch(route('admin.providers.toggle_active', $provider->id))->assertRedirect();
        $this->assertDatabaseHas('service_providers', ['id' => $provider->id, 'is_active' => 1]);

        $wasFeatured = (bool) ServiceProvider::find($provider->id)->is_featured;
        $this->patch(route('admin.providers.toggle_featured', $provider->id))->assertRedirect();
        $this->assertDatabaseHas('service_providers', ['id' => $provider->id, 'is_featured' => !$wasFeatured]);

        // Bulk hide + show
        $this->post(route('admin.providers.bulk'), ['bulk_action' => 'hide', 'ids' => [$provider->id]])->assertRedirect();
        $this->assertDatabaseHas('service_providers', ['id' => $provider->id, 'is_active' => 0]);
        $this->post(route('admin.providers.bulk'), ['bulk_action' => 'show', 'ids' => [$provider->id]])->assertRedirect();
        $this->assertDatabaseHas('service_providers', ['id' => $provider->id, 'is_active' => 1]);

        // Destroy removes the provider (account kept)
        $userId = $provider->user_id;
        $this->delete(route('admin.providers.destroy', $provider->id))->assertRedirect();
        $this->assertDatabaseMissing('service_providers', ['id' => $provider->id]);
        $this->assertDatabaseHas('users', ['id' => $userId]);
    }

    /* ------------------------------------------------------------------ */
    /*  6. Login activity + presence heartbeat                             */
    /* ------------------------------------------------------------------ */

    public function test_login_activity_lists_providers(): void
    {
        $provider = $this->makeProvider();
        $provider->user->update([
            'role' => 'service_provider',
            'login_count' => 3,
            'last_login_at' => now(),
            'last_seen_at' => now()->subMinute(),
        ]);

        $this->get(route('admin.login_activity.index'))
            ->assertOk()
            ->assertSee($provider->company_name);
    }

    public function test_presence_heartbeat_updates_last_seen(): void
    {
        $provider = $this->makeProvider();
        $user = $provider->user;
        $user->forceFill(['last_seen_at' => now()->subMinutes(10)])->save();

        $this->actingAs($user)->get(route('home'))->assertOk();

        $this->assertGreaterThan(now()->subMinutes(1), User::find($user->id)->last_seen_at);
    }

    /* ------------------------------------------------------------------ */
    /*  7. Comments: show page + bulk approval                             */
    /* ------------------------------------------------------------------ */

    public function test_comments_show_and_bulk_approve(): void
    {
        $provider = $this->makeProvider();
        $comment = Comment::create([
            'commentable_type' => ServiceProvider::class,
            'commentable_id' => $provider->id,
            'user_id' => $this->makeClient()->id,
            'content' => 'pending comment',
            'is_active' => false,
        ]);

        $this->get(route('admin.comments.show', $comment->id))->assertOk();

        $other = Comment::create([
            'commentable_type' => ServiceProvider::class,
            'commentable_id' => $provider->id,
            'user_id' => $this->makeClient()->id,
            'content' => 'second pending',
            'is_active' => false,
        ]);

        $this->post(route('admin.comments.bulk'), ['bulk_action' => 'approve', 'ids' => [$comment->id, $other->id]])
            ->assertRedirect();

        $this->assertSame(2, Comment::where('is_active', true)->count());
    }

    /* ------------------------------------------------------------------ */
    /*  8. Notification read-receipts page                                 */
    /* ------------------------------------------------------------------ */

    public function test_notification_show_page(): void
    {
        $notification = AdminNotification::factory()->create();

        $this->get(route('admin.notifications.show', $notification->id))->assertOk();
    }

    /* ------------------------------------------------------------------ */
    /*  9. Blog trash + restore                                             */
    /* ------------------------------------------------------------------ */

    public function test_blog_trash_and_restore(): void
    {
        $post = Post::factory()->create();
        $this->actingAs($this->admin)->delete(route('admin.blog.posts.destroy', $post));

        $this->assertSoftDeleted('posts', ['id' => $post->id]);

        $this->get(route('admin.blog.posts.trash'))->assertOk();

        $this->post(route('admin.blog.posts.restore', $post->id))->assertRedirect();
        $this->assertNull(Post::withTrashed()->find($post->id)->deleted_at);
    }

    /* ------------------------------------------------------------------ */
    /* 10. Review approval fires engagement email once (flag guards)       */
    /* ------------------------------------------------------------------ */

    public function test_review_approval_stamps_send_once_guards(): void
    {
        Mail::fake();

        $provider = $this->makeProvider();
        $provider->user->update(['role' => 'service_provider']);

        $client = $this->makeClient();

        $review = Review::factory()->forProvider($provider->id)->byClient($client->id)->pending()->create();

        $this->post(route('admin.reviews.approve', $review->id))->assertRedirect();

        $this->assertNotNull(User::find($client->id)->first_review_email_sent_at);
        $this->assertNotNull(ServiceProvider::find($provider->id)->first_review_received_email_sent_at);

        $flag = ServiceProvider::find($provider->id)->first_review_received_email_sent_at;

        // Approving other reviews must not fire again for previous milestones
        $this->post(route('admin.reviews.approve', $review->id))->assertRedirect();
        $this->assertEquals($flag->format('U'), ServiceProvider::find($provider->id)->first_review_received_email_sent_at->format('U'));
    }
}