<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProviderOfferSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private ServiceProvider $provider;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::disk('public')->put('profiles/test.jpg', 'test photo');
        $this->provider = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true]),
            'profile_image' => 'profiles/test.jpg', 'experience_years' => 5,
            'bio' => 'A professional with five years of experience.', 'is_active' => true,
        ]);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'submission_token' => (string) \Illuminate\Support\Str::uuid(),
            'deal_type' => 'percent', 'deal_value' => 20, 'title_en' => 'Provider exclusive offer',
            'summary_en' => 'Save twenty percent on your next visit.', 'cta_type' => 'whatsapp',
            'starts_at' => today()->toDateString(), 'ends_at' => today()->addMonth()->toDateString(),
        ], $overrides);
    }

    private function submit(): Offer
    {
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload())
            ->assertRedirect(route('provider.offers.index'))->assertSessionHasNoErrors();

        return Offer::latest('id')->firstOrFail();
    }

    public function test_guest_is_sent_to_provider_registration_with_return_destination(): void
    {
        $this->get(route('provider.offers.create'))
            ->assertRedirect(route('register', ['role' => 'service_provider', 'form' => 'register']))
            ->assertSessionHas('url.intended', route('provider.offers.create'));
        $this->post(route('provider.offers.store'), $this->payload())->assertRedirect();
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_client_is_sent_to_working_enrollment_page_and_cannot_submit(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $this->actingAs($client)->get(route('provider.offers.create'))->assertRedirect(route('provider.enroll'));
        $this->get(route('provider.enroll'))->assertOk()->assertSee(route('provider.enroll.store'), false);
        $this->post(route('provider.offers.store'), $this->payload())->assertRedirect(route('provider.enroll'));
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_new_provider_registration_returns_to_ad_and_requires_profile_completion(): void
    {
        Location::firstOrCreate(['city' => 'Ottawa'], ['is_active' => true]);
        $this->withSession(['url.intended' => route('provider.offers.create')])->post(route('register'), [
            'name' => 'New Provider', 'email' => 'new-provider@example.com',
            'mobile' => '+16135550139', 'profession' => 'other', 'city' => 'Ottawa',
            'role' => 'service_provider', 'terms' => 1,
            'password' => 'SecureSignup123!', 'password_confirmation' => 'SecureSignup123!',
        ])->assertSessionHasNoErrors()->assertRedirect(route('provider.offers.create'));
        $provider = User::where('email', 'new-provider@example.com')->firstOrFail()->serviceProvider;
        $this->get(route('provider.offers.create'))
            ->assertRedirect(route('service-providers.show', $provider).'#edit-basic-info');
    }

    public function test_client_enrolls_using_existing_account(): void
    {
        Location::factory()->create(['city' => 'Ottawa', 'is_active' => true]);
        $client = User::factory()->create(['role' => 'client']);
        $this->actingAs($client)->post(route('provider.enroll.store'), [
            'name' => 'Example Provider', 'profession' => 'other', 'city' => 'Ottawa',
            'mobile' => '+16135550128', 'terms' => 1, 'role' => 'admin',
        ])->assertSessionHasNoErrors()->assertRedirect(route('provider.offers.create'));
        $this->assertSame('service_provider', $client->fresh()->role);
        $this->assertNotNull($client->fresh()->serviceProvider);
        $this->assertCount(1, ServiceProvider::where('user_id', $client->id)->get());
    }

    public function test_enrollment_requires_provider_details_and_terms(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $this->actingAs($client)->post(route('provider.enroll.store'), [])
            ->assertSessionHasErrors(['name', 'profession', 'city', 'mobile', 'terms']);
        $this->assertSame('client', $client->fresh()->role);
    }

    public static function incompleteProfiles(): array
    {
        return [
            'no photo' => [['profile_image' => null]],
            'missing photo file' => [['profile_image' => 'missing.jpg']],
            'no experience' => [['experience_years' => null]],
            'no description' => [['bio' => null]],
            'blank description' => [['bio' => '   ']],
            'registration placeholder' => [['bio' => 'Service provider profile. Please complete your details.']],
        ];
    }

    #[DataProvider('incompleteProfiles')]
    public function test_incomplete_profiles_cannot_get_or_post_form(array $fields): void
    {
        $this->provider->update($fields);
        $target = route('service-providers.show', $this->provider).'#edit-basic-info';
        $this->actingAs($this->provider->user)->get(route('provider.offers.create'))
            ->assertRedirect($target)->assertSessionHas('offer_profile_completion', true)->assertSessionHas('error');
        $this->post(route('provider.offers.store'), $this->payload())->assertRedirect($target);
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_zero_years_is_a_valid_explicit_experience_value_and_form_is_shared(): void
    {
        $this->provider->update(['experience_years' => 0]);
        $this->actingAs($this->provider->user)->get(route('provider.offers.create'))
            ->assertOk()->assertViewIs('admin.offers.form')->assertViewHas('providerMode', true)
            ->assertSee(route('provider.offers.store'), false)->assertSee(__('offer_submission.review_notice'))
            ->assertDontSee('name="mark_verified"', false)->assertDontSee('name="is_active"', false)
            ->assertDontSee('provider_search', false);
    }

    public function test_provider_cannot_forge_owner_approval_or_publication(): void
    {
        $other = ServiceProvider::factory()->create();
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload([
            'service_provider_id' => $other->id, 'is_active' => true, 'is_featured' => true,
            'review_status' => 'approved', 'reviewed_by' => $this->admin->id, 'reviewed_at' => now(),
            'created_by' => $this->admin->id, 'mark_verified' => true, 'verified_at' => now(),
            'slug' => 'forged', 'sort_order' => 1,
            'image' => UploadedFile::fake()->image('offer.jpg'),
        ]))->assertRedirect(route('provider.offers.index'))->assertSessionHasNoErrors()->assertSessionHas('success');
        $offer = Offer::firstOrFail();
        $this->assertEquals($this->provider->id, $offer->service_provider_id);
        $this->assertEquals($this->provider->user_id, $offer->created_by);
        $this->assertSame('pending', $offer->status());
        $this->assertFalse($offer->is_active);
        $this->assertFalse($offer->is_featured);
        $this->assertNull($offer->verified_at);
        $this->assertNull($offer->reviewed_at);
        $this->assertNull($offer->reviewed_by);
        $this->assertNotSame('forged', $offer->slug);
        Storage::disk('public')->assertExists($offer->image_path);
    }

    public function test_shared_validation_rejects_bad_value_dates_and_links(): void
    {
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload([
            'deal_value' => 101, 'ends_at' => today()->subDay()->toDateString(),
            'cta_type' => 'url',
        ]))->assertSessionHasErrors(['deal_value', 'ends_at', 'cta_type']);
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_pending_stays_private_after_24_hours_even_if_active_flag_is_on(): void
    {
        $offer = $this->submit();
        $offer->update(['is_active' => true, 'is_featured' => true]);
        $this->travel(25)->hours();
        $this->assertFalse($offer->fresh()->isLive());
        $this->assertSame(0, Offer::live()->count());
        $this->assertCount(0, Offer::forHomePage());
        $this->get(route('offers.show', $offer->slug))->assertNotFound();
        $this->get(route('offers.index'))->assertDontSee($offer->title_en);
        $this->assertSame(1, Offer::withStatus('pending')->count());
        $this->assertSame(0, Offer::withStatus('live')->count());
        $this->assertSame(0, Offer::withStatus('draft')->count());
    }

    public function test_admin_approval_publishes_and_records_reviewer(): void
    {
        $offer = $this->submit();
        $this->actingAs($this->admin)->get(route('admin.offers.index', ['status' => 'pending']))->assertOk()->assertSee($offer->title_en);
        $this->get(route('admin.offers.edit', $offer))->assertOk()->assertSee('Save and approve');
        $this->patch(route('admin.offers.review', $offer), ['decision' => 'approved', 'is_featured' => 1])
            ->assertRedirect(route('admin.offers.edit', $offer));
        $offer->refresh();
        $this->assertTrue($offer->isLive());
        $this->assertEquals($this->admin->id, $offer->reviewed_by);
        $this->assertNotNull($offer->reviewed_at);
        $this->assertCount(1, Offer::forHomePage());
        $this->actingAs($this->provider->user)->get(route('offers.show', $offer->slug))->assertOk();
    }

    public function test_approval_respects_future_start_date(): void
    {
        $offer = $this->submit();
        $offer->update(['starts_at' => today()->addWeek()]);
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), ['decision' => 'approved'])->assertRedirect();
        $this->assertSame('scheduled', $offer->fresh()->status());
        $this->assertFalse($offer->fresh()->isLive());
    }

    public function test_rejection_requires_reason_and_provider_sees_it(): void
    {
        $offer = $this->submit();
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), ['decision' => 'rejected'])
            ->assertSessionHasErrors('rejection_reason');
        $this->patch(route('admin.offers.review', $offer), ['decision' => 'rejected', 'rejection_reason' => 'Please clarify the offer terms.'])->assertRedirect();
        $this->assertSame('rejected', $offer->fresh()->status());
        $this->actingAs($this->provider->user)->get(route('provider.offers.index'))->assertOk()->assertSee('Please clarify the offer terms.');
        $this->get(route('offers.show', $offer->slug))->assertNotFound();
    }

    public function test_review_is_admin_only_and_cannot_be_repeated(): void
    {
        $offer = $this->submit();
        $this->patch(route('admin.offers.review', $offer), ['decision' => 'approved'])->assertForbidden();
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), ['decision' => 'approved'])->assertRedirect();
        $this->patch(route('admin.offers.review', $offer), ['decision' => 'rejected', 'rejection_reason' => 'Repeated'])
            ->assertRedirect(route('admin.offers.edit', $offer))->assertSessionHas('error', __('offer_submission.already_reviewed'));
        $this->assertSame('approved', $offer->fresh()->review_status);
    }

    public function test_toggle_and_bulk_activation_do_not_bypass_pending_review(): void
    {
        $offer = $this->submit();
        $this->actingAs($this->admin)->patch(route('admin.offers.toggle', $offer))->assertRedirect(route('admin.offers.edit', $offer));
        $this->post(route('admin.offers.bulk'), ['action' => 'activate', 'ids' => [$offer->id]])->assertRedirect();
        $this->assertFalse($offer->fresh()->isLive());
        $this->assertSame('pending', $offer->fresh()->status());
    }

    public function test_my_ads_only_shows_current_providers_ads(): void
    {
        $offer = $this->submit();
        $other = ServiceProvider::factory()->create(['user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true]), 'is_active' => true]);
        $this->actingAs($other->user)->get(route('provider.offers.index'))->assertOk()->assertDontSee($offer->title_en);
        $this->actingAs($this->provider->user)->get(route('provider.offers.index'))->assertOk()->assertSee($offer->title_en);
    }

    public function test_inactive_provider_cannot_submit(): void
    {
        $this->provider->update(['is_active' => false]);
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload())->assertForbidden();
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_my_ads_renders_published_and_pending_offers_with_strict_loading(): void
    {
        $published = $this->submit();
        $published->update(['review_status' => 'approved', 'is_active' => true]);
        $pending = $this->submit();
        $this->get(route('provider.offers.index'))->assertOk()
            ->assertSee(route('offers.show', $published->slug), false)
            ->assertDontSee(route('offers.show', $pending->slug), false);
    }

    public function test_public_cta_is_available_in_all_languages(): void
    {
        foreach (['ar', 'en', 'fr'] as $locale) {
            $this->get(route('offers.index', ['lang' => $locale]))->assertOk()
                ->assertSee(route('provider.offers.create'), false);
        }
    }

    public static function validEditorChoices(): array
    {
        $cases = [];
        foreach (Offer::TYPES as $type) {
            foreach (Offer::CTAS as $cta) {
                $cases["$type / $cta"] = [[
                    'deal_type' => $type, 'deal_value' => in_array($type, ['percent', 'fixed']) ? 25 : null,
                    'badge_text_en' => $type === 'custom' ? 'Two for one' : null,
                    'cta_type' => $cta,
                ]];
            }
        }
        return $cases;
    }

    #[DataProvider('validEditorChoices')]
    public function test_every_deal_and_button_combination_can_be_submitted(array $fields): void
    {
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload($fields))
            ->assertSessionHasNoErrors()->assertRedirect(route('provider.offers.index'));
        $offer = Offer::firstOrFail();
        $this->assertSame($fields['deal_type'], $offer->deal_type);
        $this->assertSame($fields['cta_type'], $offer->cta_type);
        $this->assertSame('pending', $offer->status());
        $this->assertFalse($offer->isLive());
    }

    public static function optionalDates(): array
    {
        return ['neither' => [false, false], 'start only' => [true, false], 'end only' => [false, true], 'same day' => [true, true]];
    }

    #[DataProvider('optionalDates')]
    public function test_optional_dates_and_same_day_offer_are_valid(bool $start, bool $end): void
    {
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload([
            'starts_at' => $start ? today()->toDateString() : '',
            'ends_at' => $end ? today()->toDateString() : '',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('provider.offers.index'));
        $this->assertDatabaseCount('offers', 1);
    }

    public function test_switching_to_free_and_profile_ignores_old_hidden_values(): void
    {
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload([
            'deal_type' => 'free', 'deal_value' => -5, 'badge_text_en' => str_repeat('a', 41),
            'cta_type' => 'profile',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('provider.offers.index'));
        $offer = Offer::firstOrFail();
        $this->assertNull($offer->deal_value);
        $this->assertNull($offer->badge_text_en);
        $this->assertNull($offer->cta_url);
    }

    public static function invalidEditorFields(): array
    {
        return [
            'missing title' => [['title_en' => '   '], 'title_en'],
            'long title' => [['title_en' => str_repeat('a', 121)], 'title_en'],
            'missing summary' => [['summary_en' => ''], 'summary_en'],
            'long summary' => [['summary_en' => str_repeat('a', 201)], 'summary_en'],
            'long details' => [['details_en' => str_repeat('a', 5001)], 'details_en'],
            'long terms' => [['terms_en' => str_repeat('a', 3001)], 'terms_en'],
            'unknown deal' => [['deal_type' => 'unknown'], 'deal_type'],
            'missing value' => [['deal_value' => null], 'deal_value'],
            'zero value' => [['deal_value' => 0], 'deal_value'],
            'negative value' => [['deal_value' => -1], 'deal_value'],
            'percentage overflow' => [['deal_value' => 100.01], 'deal_value'],
            'fixed overflow' => [['deal_type' => 'fixed', 'deal_value' => 100001], 'deal_value'],
            'missing custom badge' => [['deal_type' => 'custom', 'badge_text_en' => ''], 'badge_text_en'],
            'unknown button' => [['cta_type' => 'unknown'], 'cta_type'],
            'url button not allowed' => [['cta_type' => 'url'], 'cta_type'],
            'array title' => [['title_en' => ['unexpected']], 'title_en'],
            'bad start' => [['starts_at' => 'not a date'], 'starts_at'],
            'bad end' => [['ends_at' => 'not a date'], 'ends_at'],
        ];
    }

    #[DataProvider('invalidEditorFields')]
    public function test_invalid_fields_never_create_an_offer(array $fields, string $error): void
    {
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload($fields))
            ->assertSessionHasErrors($error);
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_invalid_or_oversized_cover_is_rejected_without_storing_files(): void
    {
        foreach ([UploadedFile::fake()->create('script.php', 1, 'text/plain'), UploadedFile::fake()->image('large.jpg')->size(5121)] as $file) {
            $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload(['image' => $file]))
                ->assertSessionHasErrors('image');
        }
        $this->assertDatabaseCount('offers', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('offers'));
    }

    public function test_storage_failure_does_not_create_an_offer_with_a_missing_cover(): void
    {
        $disk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $disk->shouldReceive('exists')->with('profiles/test.jpg')->andReturnTrue();
        $disk->shouldReceive('put')->once()->andReturnFalse();
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload([
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]))->assertSessionHasErrors('image');
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_losing_required_photo_after_opening_form_blocks_submission(): void
    {
        $this->actingAs($this->provider->user)->get(route('provider.offers.create'))->assertOk();
        Storage::disk('public')->delete('profiles/test.jpg');
        $this->post(route('provider.offers.store'), $this->payload())
            ->assertRedirect(route('service-providers.show', $this->provider).'#edit-basic-info');
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_profile_completion_returns_to_offer_form(): void
    {
        $this->provider->update(['bio' => null, 'experience_years' => null]);
        $this->actingAs($this->provider->user)->get(route('provider.offers.create'))->assertRedirect();
        $this->put(route('service-providers.profile.update', $this->provider), [
            'business_name' => 'Example Business', 'phone' => '+16135550166',
            'whatsapp_country_code' => '+1', 'whatsapp_number' => '6135550166',
            'bio' => 'A completed description of my professional services.', 'experience_years' => 0,
        ])->assertSessionHasNoErrors()->assertRedirect(route('provider.offers.create'))
            ->assertSessionMissing('offer_profile_completion');
        $this->actingAs($this->provider->user->fresh())->get(route('provider.offers.create'))->assertOk();
    }

    public function test_inactive_user_cannot_access_or_submit_ads(): void
    {
        $this->provider->user->update(['is_active' => false]);
        $this->actingAs($this->provider->user->fresh());
        $this->get(route('provider.offers.create'))->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertGuest();
        $this->get(route('provider.offers.index'))->assertRedirect(route('login'));
        $this->post(route('provider.offers.store'), $this->payload())->assertRedirect(route('login'));
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_pending_offer_cannot_be_claimed_liked_commented_or_favorited(): void
    {
        $offer = $this->submit();
        $client = User::factory()->create(['role' => 'client']);
        $this->actingAs($client);
        $this->get(route('offers.go', $offer->slug))->assertRedirect(route('offers.show', $offer->slug));
        $this->get(route('offers.show', $offer->slug))->assertNotFound();
        $this->postJson('/offers/'.$offer->slug.'/like')->assertNotFound();
        $this->postJson('/offers/'.$offer->slug.'/comments', ['body' => 'A comment'])->assertNotFound();
        $this->postJson(route('offers.favorite', $offer->slug))->assertOk()->assertJson(['saved' => false]);
        $this->assertDatabaseCount('offer_likes', 0);
        $this->assertDatabaseCount('offer_comments', 0);
        $this->assertDatabaseCount('offer_favorites', 0);
    }

    public function test_provider_cannot_call_any_admin_offer_mutation(): void
    {
        $offer = $this->submit();
        $this->put(route('admin.offers.update', $offer), $this->payload(['is_active' => true]))->assertForbidden();
        $this->delete(route('admin.offers.destroy', $offer))->assertForbidden();
        $this->patch(route('admin.offers.toggle', $offer))->assertForbidden();
        $this->patch(route('admin.offers.feature', $offer))->assertForbidden();
        $this->post(route('admin.offers.duplicate', $offer))->assertForbidden();
        $this->post(route('admin.offers.bulk'), ['action' => 'activate', 'ids' => [$offer->id]])->assertForbidden();
        $this->assertSame('pending', $offer->fresh()->status());
        $this->assertDatabaseCount('offers', 1);
    }

    public function test_admin_edit_does_not_approve_pending_offer_even_with_forged_review_fields(): void
    {
        $offer = $this->submit();
        $this->actingAs($this->admin)->put(route('admin.offers.update', $offer), $this->payload([
            'service_provider_id' => $this->provider->id, 'review_status' => 'approved',
            'is_active' => true, 'is_featured' => true,
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('pending', $offer->fresh()->status());
        $this->assertFalse($offer->fresh()->isLive());
    }

    public function test_review_rejects_unknown_decision_and_whitespace_reason(): void
    {
        $offer = $this->submit();
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), ['decision' => 'live'])
            ->assertSessionHasErrors('decision');
        $this->patch(route('admin.offers.review', $offer), ['decision' => 'rejected', 'rejection_reason' => '   '])
            ->assertSessionHasErrors('rejection_reason');
        $this->assertSame('pending', $offer->fresh()->status());
    }

    public function test_rejection_reason_and_titles_are_escaped_in_provider_list(): void
    {
        $offer = $this->submit();
        $attack = '<script>alert(1)</script>';
        $offer->update(['title_en' => $attack, 'review_status' => 'rejected', 'rejection_reason' => $attack]);
        $this->get(route('provider.offers.index'))->assertOk()->assertSee(e($attack), false)->assertDontSee($attack, false);
    }

    public function test_provider_list_paginates_without_leaking_other_providers(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            Offer::create(\Illuminate\Support\Arr::except($this->payload(), 'submission_token') + ['slug' => 'owned-offer-'.$i, 'service_provider_id' => $this->provider->id, 'review_status' => 'pending']);
        }
        $other = Offer::create(\Illuminate\Support\Arr::except($this->payload(['title_en' => 'Another provider private offer']), 'submission_token') + [
            'slug' => 'another-provider-private-offer', 'service_provider_id' => ServiceProvider::factory()->create()->id, 'review_status' => 'pending',
        ]);
        $this->actingAs($this->provider->user)->get(route('provider.offers.index'))->assertOk()
            ->assertViewHas('offers', fn ($offers) => $offers->count() === 12 && $offers->total() === 13)
            ->assertDontSee($other->title_en);
        $this->get(route('provider.offers.index', ['page' => 2]))->assertOk()
            ->assertViewHas('offers', fn ($offers) => $offers->count() === 1)->assertDontSee($other->title_en);
    }

    public function test_retrying_the_same_submission_does_not_duplicate_offer_or_image(): void
    {
        $payload = $this->payload(['image' => UploadedFile::fake()->image('cover.jpg')]);
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $payload)->assertSessionHasNoErrors();
        $this->post(route('provider.offers.store'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('provider.offers.index'));
        $this->assertDatabaseCount('offers', 1);
        $this->assertCount(1, Storage::disk('public')->allFiles('offers'));
    }

    public function test_new_form_can_submit_a_separate_offer_with_the_same_title(): void
    {
        $first = $this->submit();
        $second = $this->submit();
        $this->assertNotSame($first->slug, $second->slug);
        $this->assertDatabaseCount('offers', 2);
    }

    public function test_storage_exception_is_a_recoverable_form_error(): void
    {
        $disk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $disk->shouldReceive('exists')->with('profiles/test.jpg')->andReturnTrue();
        $disk->shouldReceive('put')->once()->andThrow(\League\Flysystem\UnableToWriteFile::atLocation('offers/test.webp'));
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload(['image' => UploadedFile::fake()->image('cover.jpg')]))
            ->assertSessionHasErrors('image');
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_failed_database_write_removes_the_uploaded_cover(): void
    {
        $this->withoutExceptionHandling();
        Offer::creating(fn () => throw new \RuntimeException('Simulated database failure'));
        try {
            $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload(['image' => UploadedFile::fake()->image('cover.jpg')]));
            $this->fail('The simulated database failure should be raised.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated database failure', $e->getMessage());
        }
        $this->assertDatabaseCount('offers', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('offers'));
    }

    public function test_missing_or_invalid_submission_token_is_rejected(): void
    {
        foreach ([null, 'not-a-token'] as $token) {
            $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $this->payload(['submission_token' => $token]))
                ->assertSessionHasErrors('submission_token');
        }
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_submission_limit_returns_429_instead_of_accepting_more_ads(): void
    {
        $this->actingAs($this->provider->user);
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('provider.offers.store'), $this->payload())->assertRedirect();
        }
        $this->post(route('provider.offers.store'), $this->payload())->assertStatus(429);
        $this->assertDatabaseCount('offers', 5);
    }

    public function test_admin_can_duplicate_a_provider_submission_without_copying_its_token(): void
    {
        $original = $this->submit();
        $this->actingAs($this->admin)->post(route('admin.offers.duplicate', $original))->assertRedirect();
        $copy = Offer::latest('id')->firstOrFail();
        $this->assertNotSame($original->id, $copy->id);
        $this->assertNull($copy->submission_key);
        $this->assertSame('draft', $copy->status());
        $this->assertSame('approved', $copy->review_status);
        $this->assertNull($copy->submitted_at);
    }

    public function test_submission_tokens_are_scoped_to_the_provider(): void
    {
        $payload = $this->payload();
        $this->actingAs($this->provider->user)->post(route('provider.offers.store'), $payload)->assertRedirect();
        $other = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true]),
            'profile_image' => 'profiles/test.jpg', 'experience_years' => 3,
            'bio' => 'Another professional provider description.', 'is_active' => true,
        ]);
        $this->actingAs($other->user)->post(route('provider.offers.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseCount('offers', 2);
        $this->assertSame(2, Offer::distinct()->count('submission_key'));
    }

    public function test_pending_ads_do_not_record_public_impressions_or_shares(): void
    {
        $offer = $this->submit();
        $this->actingAs(User::factory()->create(['role' => 'client']));
        foreach (['impression', 'share'] as $event) {
            $this->postJson(route('offers.track'), ['event' => $event, 'ids' => [$offer->id]])
                ->assertOk()->assertJson(['recorded' => 0]);
        }
        $this->assertDatabaseCount('offer_events', 0);
    }

    public function test_approval_does_not_override_provider_suspension_or_expiry(): void
    {
        $offer = $this->submit();
        $this->provider->update(['is_active' => false]);
        $this->actingAs($this->admin)->patch(route('admin.offers.review', $offer), ['decision' => 'approved'])->assertRedirect();
        $this->assertFalse($offer->fresh()->isLive());
        $this->assertSame(0, Offer::live()->count());
        $this->provider->update(['is_active' => true]);
        $offer->update(['starts_at' => today()->subWeek(), 'ends_at' => today()->subDay()]);
        $this->assertSame('expired', $offer->fresh()->status());
        $this->assertFalse($offer->fresh()->isLive());
    }

    public function test_csrf_protection_is_required_for_submission_and_review(): void
    {
        $this->app->bind(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, fn ($app) =>
            new class($app, $app['encrypter']) extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken {
                protected function runningUnitTests() { return false; }
            });
        $this->actingAs($this->provider->user)->postJson(route('provider.offers.store'), $this->payload())->assertStatus(419);
        $offer = Offer::create(\Illuminate\Support\Arr::except($this->payload(), 'submission_token') + [
            'slug' => 'csrf-pending-offer', 'service_provider_id' => $this->provider->id, 'review_status' => 'pending',
        ]);
        $this->actingAs($this->admin)->patchJson(route('admin.offers.review', $offer), ['decision' => 'approved'])->assertStatus(419);
        $this->assertSame('pending', $offer->fresh()->review_status);
    }

    public function test_complete_signup_cannot_be_repeated_or_used_to_change_admin_role(): void
    {
        $this->actingAs($this->provider->user)->get(route('provider.enroll'))->assertRedirect(route('provider.offers.create'));
        $this->post(route('provider.enroll.store'), [])->assertForbidden();
        $this->actingAs($this->admin)->post(route('provider.enroll.store'), [])->assertForbidden();
        $this->assertSame('admin', $this->admin->fresh()->role);
        $this->assertDatabaseCount('service_providers', 1);
    }
}
