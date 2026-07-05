<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\ServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLocationsTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Test admin can view locations list
     */
    public function test_admin_can_view_locations_list()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.locations'))
            ->assertStatus(200)
            ->assertViewIs('admin.locations.index');
    }

    /**
     * Test non-admin cannot view locations list
     */
    public function test_non_admin_cannot_view_locations_list()
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'client']);

        $this->actingAs($user)
            ->get(route('admin.locations'))
            ->assertForbidden();
    }

    /**
     * Test admin can create a location
     */
    public function test_admin_can_create_location()
    {
        $data = [
            'city' => 'Toronto',
            'country' => 'Canada',
            'area' => 'Ontario',
            'is_active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), $data)
            ->assertRedirect(route('admin.locations'));

        $this->assertDatabaseHas('locations', [
            'city' => 'Toronto',
            'country' => 'Canada',
            'area' => 'Ontario',
            'is_active' => true,
        ]);
    }

    /**
     * Test location city must be unique
     */
    public function test_location_city_must_be_unique()
    {
        Location::factory()->create(['city' => 'Montreal']);

        $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'city' => 'Montreal',
            ])
            ->assertSessionHasErrors('city');
    }

    /**
     * Test admin can update a location
     */
    public function test_admin_can_update_location()
    {
        $location = Location::factory()->create();

        $data = [
            'city' => 'Updated City',
            'country' => 'Updated Country',
            'is_active' => false,
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.locations.update', $location), $data)
            ->assertRedirect(route('admin.locations'));

        $location->refresh();
        $this->assertEquals('Updated City', $location->city);
        $this->assertEquals('Updated Country', $location->country);
        $this->assertFalse($location->is_active);
    }

    /**
     * Test admin can delete location with no providers
     */
    public function test_admin_can_delete_location_with_no_providers()
    {
        $location = Location::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin)
            ->delete(route('admin.locations.delete', $location))
            ->assertRedirect(route('admin.locations'));

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    /**
     * Test admin cannot delete location with providers
     */
    public function test_admin_cannot_delete_location_with_providers()
    {
        $location = Location::factory()->create(['is_active' => false]);

        // Create a service provider at this location
        $user = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.locations.delete', $location))
            ->assertRedirect();

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    /**
     * Test location city is required
     */
    public function test_location_city_is_required()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [])
            ->assertSessionHasErrors('city');
    }

    /**
     * Test location latitude validation
     */
    public function test_location_latitude_validation()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'city' => 'Test City',
                'latitude' => 91,  // Out of range
            ])
            ->assertSessionHasErrors('latitude');
    }

    /**
     * Test location longitude validation
     */
    public function test_location_longitude_validation()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'city' => 'Test City',
                'longitude' => 181,  // Out of range
            ])
            ->assertSessionHasErrors('longitude');
    }
}
