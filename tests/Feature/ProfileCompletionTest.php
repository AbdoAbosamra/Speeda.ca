<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 100% = profile photo + years of experience + description, a third each.
 */
class ProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::disk('public')->put('profiles/me.jpg', 'photo');
    }

    private function provider(array $attributes = []): ServiceProvider
    {
        return ServiceProvider::factory()->create(array_merge([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true]),
            'profile_image' => null,
            'experience_years' => null,
            'bio' => ServiceProvider::PLACEHOLDER_BIO,
            'address' => null,
            'services_offered' => null,
            'is_active' => true,
        ], $attributes))->fresh();
    }

    public function test_an_empty_profile_is_zero_percent(): void
    {
        $this->assertSame(0, $this->provider()->profile_completion_percent);
    }

    public function test_each_item_is_a_third(): void
    {
        $this->assertSame(33, $this->provider(['profile_image' => 'profiles/me.jpg'])->profile_completion_percent);
        $this->assertSame(33, $this->provider(['experience_years' => 3])->profile_completion_percent);
        $this->assertSame(33, $this->provider(['bio' => 'I fix kitchens and bathrooms.'])->profile_completion_percent);
        $this->assertSame(67, $this->provider(['experience_years' => 3, 'bio' => 'I fix kitchens.'])->profile_completion_percent);
    }

    public function test_photo_experience_and_description_are_one_hundred_percent_without_address_services_or_gallery(): void
    {
        $provider = $this->provider([
            'profile_image' => 'profiles/me.jpg', 'experience_years' => 4, 'bio' => 'Licensed electrician.',
        ]);

        $this->assertSame(100, $provider->profile_completion_percent);
        $this->assertSame([], $provider->missingOfferProfileFields());
    }

    public function test_zero_years_is_an_answer_but_the_placeholder_bio_and_a_missing_file_are_not(): void
    {
        $provider = $this->provider([
            'profile_image' => 'profiles/gone.jpg', 'experience_years' => 0, 'bio' => '  '.ServiceProvider::PLACEHOLDER_BIO.'  ',
        ]);

        $this->assertSame(['photo' => false, 'experience' => true, 'bio' => false], $provider->profileCompletionChecklist());
        $this->assertSame(33, $provider->profile_completion_percent);
        $this->assertCount(2, $provider->missingOfferProfileFields());
    }

    public function test_the_stored_percent_follows_edits(): void
    {
        $provider = $this->provider(['profile_image' => 'profiles/me.jpg']);
        $provider->update(['experience_years' => 2, 'bio' => 'Ten years of carpentry.']);
        $this->assertSame(100, $provider->fresh()->profile_completion_percent);

        $provider->update(['bio' => '']);
        $this->assertSame(67, $provider->fresh()->profile_completion_percent);
    }

    public function test_owner_sees_the_three_item_checklist_on_their_profile(): void
    {
        $provider = $this->provider(['profile_image' => 'profiles/me.jpg']);

        $this->actingAs($provider->user)->get(route('service-providers.show', $provider))
            ->assertOk()
            ->assertSee('data-completion-item="photo"', false)
            ->assertSee('data-completion-item="experience"', false)
            ->assertSee('data-completion-item="bio"', false)
            ->assertSee('id="bioInput"', false)
            ->assertSee('33%');
    }

    public function test_admin_dashboard_counts_each_item(): void
    {
        $this->provider(['profile_image' => 'profiles/me.jpg', 'experience_years' => 1, 'bio' => 'Done profile here.']);
        $this->provider(['experience_years' => 0]);
        $this->provider();
        Cache::forget('admin_dash_profile_health');

        $health = app(AdminDashboardService::class)->profileHealth();

        $this->assertSame(3, $health['total']);
        $this->assertSame(1, $health['complete']);
        $this->assertSame(1, $health['partial']);
        $this->assertSame(1, $health['incomplete']);
        $this->assertSame(['photo' => 1, 'experience' => 2, 'bio' => 1], $health['items']);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('100% = photo + years of experience + description')
            ->assertSee(route('admin.provider_activity_monitor.index', ['issue' => 'missing_bio']), false);
    }

    public function test_activity_monitor_filters_by_the_new_items(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $noBio = $this->provider(['company_name' => 'NoBioCo', 'profile_image' => 'profiles/me.jpg', 'experience_years' => 2]);
        $full = $this->provider(['company_name' => 'FullCo', 'profile_image' => 'profiles/me.jpg', 'experience_years' => 2, 'bio' => 'All about us.']);

        $this->actingAs($admin)->get(route('admin.provider_activity_monitor.index', ['issue' => 'missing_bio']))
            ->assertOk()->assertSee('NoBioCo')->assertDontSee('FullCo');
        $this->get(route('admin.provider_activity_monitor.index', ['issue' => 'missing_experience']))
            ->assertOk()->assertDontSee('NoBioCo')->assertDontSee('FullCo');
    }
}
