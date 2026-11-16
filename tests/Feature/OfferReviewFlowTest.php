<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The whole admin review loop: submit → (edit +) approve, or reject → provider
 * fixes and resubmits → approve; with the admin and provider told each time.
 */
class OfferReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    private ServiceProvider $provider;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::disk('public')->put('profiles/me.jpg', 'photo');
        $this->provider = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true]),
            'profile_image' => 'profiles/me.jpg', 'experience_years' => 5,
            'bio' => 'Licensed plumber in Ottawa.', 'is_active' => true,
            'whatsapp_number' => '+16135550199',
        ]);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'submission_token' => (string) Str::uuid(),
            'deal_type' => 'percent', 'deal_value' => 20, 'title_en' => 'Spring plumbing deal',
            'summary_en' => 'Twenty percent off any repair.', 'cta_type' => 'whatsapp',
            'starts_at' => today()->toDateString(), 'ends_at' => today()->addMonth()->toDateString(),
        ], $overrides);
    }

    private function submit(): Offer
    {
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload())
            ->assertRedirect(route('provider.offers.index'));

        return Offer::latest('id')->firstOrFail();
    }

    private function adminPayload(array $overrides = []): array
    {
        $p = $this->payload($overrides + ['service_provider_id' => $this->provider->id]);
        unset($p['submission_token']);

        return $p;
    }

    private function notificationsFor(ServiceProvider $provider)
    {
        return AdminNotification::visibleToUser($provider->user)->get();
    }

    public function test_admin_sees_the_queue_on_the_dashboard_and_in_the_menu(): void
    {
        $this->submit();
        Cache::flush();

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Provider offers awaiting approval')
            ->assertSee(route('admin.offers.index', ['status' => 'pending']), false);
    }

    public function test_pending_offer_page_shows_save_and_approve_and_reject(): void
    {
        $offer = $this->submit();

        $this->actingAs($this->admin)->get(route('admin.offers.edit', $offer))
            ->assertOk()
            ->assertSee('Provider submission waiting for your review')
            ->assertSee('form="offer-editor" name="review_decision" value="approved"', false)
            ->assertSee('Reject and notify the provider')
            ->assertDontSee('name="is_active" value="1"', false);
    }

    public function test_save_and_approve_keeps_the_admins_edits_and_publishes(): void
    {
        $offer = $this->submit();

        $this->actingAs($this->admin)->put(route('admin.offers.update', $offer), $this->adminPayload([
            'title_en' => 'Corrected by admin', 'is_featured' => 1, 'review_decision' => 'approved',
        ]))->assertRedirect(route('admin.offers.edit', $offer))->assertSessionHas('success', __('offer_submission.review_approved'));

        $offer->refresh();
        $this->assertSame('Corrected by admin', $offer->title_en);
        $this->assertSame(Offer::REVIEW_APPROVED, $offer->review_status);
        $this->assertTrue($offer->is_active);
        $this->assertTrue($offer->is_featured);
        $this->assertSame($this->admin->id, $offer->reviewed_by);
        $this->assertSame(Offer::STATUS_LIVE, $offer->status());
    }

    public function test_plain_save_on_a_pending_offer_does_not_approve_it(): void
    {
        $offer = $this->submit();

        $this->actingAs($this->admin)->put(route('admin.offers.update', $offer), $this->adminPayload(['title_en' => 'Just a fix']))
            ->assertRedirect();

        $this->assertSame('Just a fix', $offer->fresh()->title_en);
        $this->assertSame(Offer::STATUS_PENDING, $offer->fresh()->status());
    }

    public function test_save_and_approve_with_invalid_edits_approves_nothing(): void
    {
        $offer = $this->submit();

        $this->actingAs($this->admin)->put(route('admin.offers.update', $offer), $this->adminPayload([
            'title_en' => '', 'review_decision' => 'approved',
        ]))->assertSessionHasErrors('title_en');

        $this->assertSame(Offer::STATUS_PENDING, $offer->fresh()->status());
    }

    public function test_provider_is_notified_of_approval_only_themselves(): void
    {
        $offer = $this->submit();
        $other = ServiceProvider::factory()->create(['user_id' => User::factory()->create(['role' => 'service_provider'])]);

        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), ['decision' => 'approved'])->assertRedirect();

        $mine = $this->notificationsFor($this->provider);
        $this->assertCount(1, $mine);
        $this->assertStringContainsString('Spring plumbing deal', $mine->first()->message_en);
        $this->assertSame(__('offer_submission.notify_approved_title', [], 'ar'), $mine->first()->title_ar);
        $this->assertCount(0, $this->notificationsFor($other));
    }

    public function test_reject_then_provider_fixes_and_resubmits_then_admin_approves(): void
    {
        $offer = $this->submit();

        // 1. Reject with a reason: provider is told why.
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), [
            'decision' => 'rejected', 'rejection_reason' => 'Say what the discount covers.',
        ])->assertSessionHas('success', __('offer_submission.review_rejected'));
        $this->assertSame(Offer::STATUS_REJECTED, $offer->fresh()->status());
        $this->assertStringContainsString('Say what the discount covers.', $this->notificationsFor($this->provider)->first()->message_en);

        // 2. Provider sees the reason and an edit button.
        $this->actingAs($this->provider->user)->get(route('provider.offers.index'))
            ->assertOk()->assertSee('Say what the discount covers.')
            ->assertSee(route('provider.offers.edit', $offer), false);
        $this->get(route('provider.offers.edit', $offer))->assertOk()
            ->assertSee('Say what the discount covers.')
            ->assertSee(route('provider.offers.update', $offer), false)
            ->assertDontSee(route('admin.offers.analytics'), false);

        // 3. Resubmit: back to pending, reason cleared, still private.
        $this->put(route('provider.offers.update', $offer), $this->payload([
            'summary_en' => 'Twenty percent off labour on any repair.',
            'is_active' => 1, 'is_featured' => 1, 'review_status' => 'approved',
        ]))->assertRedirect(route('provider.offers.index'))->assertSessionHas('success', __('offer_submission.resubmitted'));
        $offer->refresh();
        $this->assertSame(Offer::STATUS_PENDING, $offer->status());
        $this->assertNull($offer->rejection_reason);
        $this->assertFalse($offer->is_active);
        $this->assertFalse($offer->is_featured);
        $this->assertSame('Twenty percent off labour on any repair.', $offer->summary_en);

        // 4. Approve.
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), ['decision' => 'approved'])->assertRedirect();
        $this->assertSame(Offer::STATUS_LIVE, $offer->fresh()->status());
    }

    public function test_admin_can_still_approve_a_rejected_offer_but_not_reject_twice(): void
    {
        $offer = $this->submit();
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), ['decision' => 'rejected', 'rejection_reason' => 'No.']);

        $this->get(route('admin.offers.edit', $offer))->assertOk()->assertSee('You rejected this submission')->assertDontSee('Reject and notify the provider');
        $this->patch(route('admin.offers.review', $offer), ['decision' => 'rejected', 'rejection_reason' => 'Again'])
            ->assertSessionHas('error', __('offer_submission.already_reviewed'));

        $this->patch(route('admin.offers.review', $offer), ['decision' => 'approved'])->assertSessionHas('success');
        $this->assertSame(Offer::STATUS_LIVE, $offer->fresh()->status());
        $this->assertNull($offer->fresh()->rejection_reason);
    }

    public function test_provider_cannot_edit_approved_or_someone_elses_offer(): void
    {
        $offer = $this->submit();
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), ['decision' => 'approved']);

        $this->actingAs($this->provider->user)->get(route('provider.offers.edit', $offer))
            ->assertRedirect(route('provider.offers.index'))->assertSessionHas('error', __('offer_submission.not_editable'));
        $this->put(route('provider.offers.update', $offer), $this->payload(['title_en' => 'Sneaky']))
            ->assertRedirect(route('provider.offers.index'));
        $this->assertNotSame('Sneaky', $offer->fresh()->title_en);

        $pending = $this->submit();
        $intruder = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true]),
            'profile_image' => 'profiles/me.jpg', 'experience_years' => 1, 'bio' => 'Other provider.', 'is_active' => true,
        ]);
        $this->actingAs($intruder->user)->get(route('provider.offers.edit', $pending))->assertNotFound();
        $this->put(route('provider.offers.update', $pending), $this->payload(['title_en' => 'Hijack']))->assertNotFound();
        $this->assertNotSame('Hijack', $pending->fresh()->title_en);
    }

    public function test_analytics_lists_pending_and_rejected_offers_without_crashing(): void
    {
        $rejected = $this->submit();
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $rejected), ['decision' => 'rejected', 'rejection_reason' => 'No.']);
        $pending = $this->submit();

        $this->actingAs($this->admin)->get(route('admin.offers.analytics', ['range' => '90']))->assertOk()
            ->assertSee('Pending review')->assertSee('Rejected');
        $this->get(route('admin.offers.analytics', ['offer_id' => $rejected->id, 'range' => '90']))->assertOk();
        $this->get(route('offers.show', $pending->slug))->assertOk()->assertSee('Pending review');
    }
}
