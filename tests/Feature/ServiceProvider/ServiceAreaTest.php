<?php

namespace Tests\Feature\ServiceProvider;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature coverage for the "Available Service Areas" feature:
 * providers may select additional locations where they are available,
 * which are stored in the `service_areas` pivot and surfaced in the
 * public location filter (with an "available in this area" badge).
 */
class ServiceAreaTest extends TestCase
{
    use RefreshDatabase;

    private int $phoneSeq = 0;

    /**
     * Resolve (or create) an active location by city name.
     *
     * Some migrations seed Ontario/Québec cities into `locations`, and `city`
     * is unique — so we reuse the existing row instead of inserting a duplicate.
     */
    private function loc(string $city): Location
    {
        $location = Location::firstOrCreate(
            ['city' => $city],
            ['is_active' => true, 'country' => 'Canada']
        );

        if (! $location->is_active) {
            $location->update(['is_active' => true]);
        }

        return $location;
    }

    /**
     * Create a provider (with an active user) based in the given primary location.
     */
    private function makeProvider(Location $primary, array $overrides = []): ServiceProvider
    {
        $this->phoneSeq++;
        $user = User::factory()->create(['is_active' => true]);

        return ServiceProvider::factory()->create(array_merge([
            'user_id' => $user->id,
            'category_id' => Category::factory(),
            'location_id' => $primary->id,
            'phone' => '+1416555' . str_pad((string) $this->phoneSeq, 4, '0', STR_PAD_LEFT),
            'whatsapp_number' => '+1438555' . str_pad((string) $this->phoneSeq, 4, '0', STR_PAD_LEFT),
        ], $overrides));
    }

    /**
     * Build a fully valid profile-update payload (resending the provider's own
     * phone/whatsapp so the unique rules pass via ignore-self).
     */
    private function validPayload(ServiceProvider $sp, array $overrides = []): array
    {
        // whatsapp_number local part = stored digits without the leading "1" country digit.
        $waLocal = substr(preg_replace('/\D/', '', (string) $sp->whatsapp_number), 1);

        return array_merge([
            'business_name' => 'Updated Business Co',
            'phone' => $sp->phone,
            'whatsapp_country_code' => '+1',
            'whatsapp_number' => $waLocal,
            'location_id' => $sp->location_id,
            'has_service_areas' => 1,
        ], $overrides);
    }

    // ─────────────────────────── Saving / syncing ───────────────────────────

    public function test_owner_can_save_multiple_service_areas(): void
    {
        $primary = $this->loc('Brampton');
        $area1 = $this->loc('Mississauga');
        $area2 = $this->loc('Oakville');

        $sp = $this->makeProvider($primary);

        $response = $this->actingAs($sp->user)->put(
            route('service-providers.profile.update', $sp),
            $this->validPayload($sp, ['service_areas' => [$area1->id, $area2->id]])
        );

        $response->assertRedirect(route('service-providers.show', $sp->id));

        $this->assertDatabaseHas('service_areas', [
            'service_provider_id' => $sp->id,
            'location_id' => $area1->id,
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('service_areas', [
            'service_provider_id' => $sp->id,
            'location_id' => $area2->id,
            'is_active' => 1,
        ]);
        $this->assertSame(2, $sp->fresh()->serviceAreas()->count());
    }

    public function test_primary_location_is_excluded_from_service_areas(): void
    {
        $primary = $this->loc('Brampton');
        $area1 = $this->loc('Mississauga');

        $sp = $this->makeProvider($primary);

        // Deliberately include the primary location in the selection.
        $this->actingAs($sp->user)->put(
            route('service-providers.profile.update', $sp),
            $this->validPayload($sp, ['service_areas' => [$primary->id, $area1->id]])
        );

        // Primary must NOT be duplicated into service_areas; only the extra one is stored.
        $this->assertDatabaseMissing('service_areas', [
            'service_provider_id' => $sp->id,
            'location_id' => $primary->id,
        ]);
        $this->assertDatabaseHas('service_areas', [
            'service_provider_id' => $sp->id,
            'location_id' => $area1->id,
        ]);
        $this->assertSame(1, $sp->fresh()->serviceAreas()->count());
    }

    public function test_unchecking_all_areas_clears_existing_ones(): void
    {
        $primary = $this->loc('Brampton');
        $area1 = $this->loc('Mississauga');

        $sp = $this->makeProvider($primary);
        $sp->locations()->sync([$area1->id => ['is_active' => true]]);
        $this->assertSame(1, $sp->serviceAreas()->count());

        // Submit the form again with the marker present but no boxes checked.
        $this->actingAs($sp->user)->put(
            route('service-providers.profile.update', $sp),
            $this->validPayload($sp) // no 'service_areas' key
        );

        $this->assertSame(0, $sp->fresh()->serviceAreas()->count());
    }

    public function test_missing_marker_leaves_service_areas_untouched(): void
    {
        $primary = $this->loc('Brampton');
        $area1 = $this->loc('Mississauga');

        $sp = $this->makeProvider($primary);
        $sp->locations()->sync([$area1->id => ['is_active' => true]]);

        // Payload WITHOUT the has_service_areas marker (e.g. a different form/path).
        $payload = $this->validPayload($sp);
        unset($payload['has_service_areas']);

        $this->actingAs($sp->user)->put(route('service-providers.profile.update', $sp), $payload);

        // Existing areas must be preserved when the section was not submitted.
        $this->assertSame(1, $sp->fresh()->serviceAreas()->count());
    }

    public function test_invalid_location_id_is_rejected_and_nothing_saved(): void
    {
        $primary = $this->loc('Brampton');
        $sp = $this->makeProvider($primary);

        $response = $this->actingAs($sp->user)
            ->from(route('service-providers.show', $sp->id))
            ->put(
                route('service-providers.profile.update', $sp),
                $this->validPayload($sp, ['service_areas' => [999999]])
            );

        $response->assertSessionHasErrors('service_areas.0');
        $this->assertSame(0, $sp->fresh()->serviceAreas()->count());
    }

    public function test_non_filterable_location_is_rejected(): void
    {
        $primary = $this->loc('Brampton');
        // Vancouver is a real, active location but is NOT part of any public filter cluster.
        $vancouver = $this->loc('Vancouver');
        $sp = $this->makeProvider($primary);

        $response = $this->actingAs($sp->user)
            ->from(route('service-providers.show', $sp->id))
            ->put(
                route('service-providers.profile.update', $sp),
                $this->validPayload($sp, ['service_areas' => [$vancouver->id]])
            );

        $response->assertSessionHasErrors('service_areas.0');
        $this->assertSame(0, $sp->fresh()->serviceAreas()->count());
    }

    public function test_non_owner_cannot_modify_service_areas(): void
    {
        $primary = $this->loc('Brampton');
        $area1 = $this->loc('Mississauga');

        $sp = $this->makeProvider($primary);
        $intruder = User::factory()->create(['is_active' => true]);

        $this->actingAs($intruder)->put(
            route('service-providers.profile.update', $sp),
            $this->validPayload($sp, ['service_areas' => [$area1->id]])
        );

        // Authorization fails in the FormRequest → nothing is persisted.
        $this->assertSame(0, $sp->fresh()->serviceAreas()->count());
    }

    // ─────────────────────────── Scope / filtering ───────────────────────────

    public function test_scope_matches_primary_location(): void
    {
        $primary = $this->loc('Mississauga');
        $other = $this->loc('Ottawa');

        $sp = $this->makeProvider($primary);
        $this->makeProvider($other); // should not match

        $ids = ServiceProvider::availableInLocations([$primary->id])->pluck('id')->all();

        $this->assertContains($sp->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_scope_matches_active_service_area(): void
    {
        $base = $this->loc('Brampton');
        $area = $this->loc('Mississauga');

        $sp = $this->makeProvider($base);
        $sp->locations()->sync([$area->id => ['is_active' => true]]);

        $ids = ServiceProvider::availableInLocations([$area->id])->pluck('id')->all();

        $this->assertContains($sp->id, $ids);
    }

    public function test_scope_ignores_inactive_service_area(): void
    {
        $base = $this->loc('Brampton');
        $area = $this->loc('Mississauga');

        $sp = $this->makeProvider($base);
        $sp->locations()->sync([$area->id => ['is_active' => false]]);

        $ids = ServiceProvider::availableInLocations([$area->id])->pluck('id')->all();

        $this->assertNotContains($sp->id, $ids);
    }

    public function test_service_area_location_ids_helper_returns_active_ids(): void
    {
        $base = $this->loc('Brampton');
        $active = $this->loc('Mississauga');
        $inactive = $this->loc('Oakville');

        $sp = $this->makeProvider($base);
        $sp->locations()->sync([
            $active->id => ['is_active' => true],
            $inactive->id => ['is_active' => false],
        ]);

        $ids = $sp->fresh()->serviceAreaLocationIds();

        $this->assertContains($active->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    // ─────────────────────────── Public listing ───────────────────────────

    public function test_listing_filter_includes_provider_available_via_service_area(): void
    {
        $mississauga = $this->loc('Mississauga');
        $brampton = $this->loc('Brampton');
        $ottawa = $this->loc('Ottawa');

        // A: based in Mississauga (matches by primary)
        $based = $this->makeProvider($mississauga, ['company_name' => 'BasedInMiss Co']);
        // B: based in Brampton, available in Mississauga (matches by service area)
        $available = $this->makeProvider($brampton, ['company_name' => 'AvailableInMiss Co']);
        $available->locations()->sync([$mississauga->id => ['is_active' => true]]);
        // C: based in Ottawa, unrelated (must NOT match)
        $unrelated = $this->makeProvider($ottawa, ['company_name' => 'OttawaOnly Co']);

        $response = $this->get(route('service-providers.index', ['location' => 'cluster_mississauga']));

        $response->assertOk();
        $response->assertSee('BasedInMiss Co');
        $response->assertSee('AvailableInMiss Co');
        $response->assertDontSee('OttawaOnly Co');
    }

    public function test_available_badge_shows_only_for_service_area_match(): void
    {
        $mississauga = $this->loc('Mississauga');
        $brampton = $this->loc('Brampton');

        // Based here → no badge; available here (different base) → badge.
        $this->makeProvider($mississauga, ['company_name' => 'BasedInMiss Co']);
        $available = $this->makeProvider($brampton, ['company_name' => 'AvailableInMiss Co']);
        $available->locations()->sync([$mississauga->id => ['is_active' => true]]);

        $response = $this->get(route('service-providers.index', ['location' => 'cluster_mississauga']));

        $response->assertOk();
        // The "available in this area" label appears (for the service-area match).
        $response->assertSee(__('service_provider.available_in_area'));
    }

    public function test_unfiltered_listing_has_no_available_badge(): void
    {
        $brampton = $this->loc('Brampton');
        $mississauga = $this->loc('Mississauga');

        $available = $this->makeProvider($brampton, ['company_name' => 'AvailableInMiss Co']);
        $available->locations()->sync([$mississauga->id => ['is_active' => true]]);

        $response = $this->get(route('service-providers.index'));

        $response->assertOk();
        $response->assertDontSee(__('service_provider.available_in_area'));
    }
}
