<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceProviderProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected ServiceProvider $serviceProvider;
    protected ServiceProvider $otherServiceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        // Create categories
        $category = Category::factory()->create([
            'name' => 'Plumbing',
            'parent_id' => 1,
            'is_active' => true,
        ]);

        $location = Location::factory()->create(['city' => 'Montreal']);

        // Create users and service providers
        $this->user = User::factory()->create(['role' => 'service_provider']);
        $this->serviceProvider = ServiceProvider::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'company_name' => 'Test Provider',
            'phone' => '1234567890',
        ]);

        $this->otherUser = User::factory()->create(['role' => 'service_provider']);
        $this->otherServiceProvider = ServiceProvider::factory()->create([
            'user_id' => $this->otherUser->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
        ]);
    }

    #[Test]
    public function test_owner_can_view_own_profile()
    {
        $response = $this->actingAs($this->user)
            ->get(route('service-providers.show', $this->serviceProvider));

        $response->assertStatus(200)
            ->assertSee($this->serviceProvider->company_name);
    }

    #[Test]
    public function test_other_provider_can_view_public_profile()
    {
        // Provider profiles are public; any authenticated user can view them.
        $response = $this->actingAs($this->otherUser)
            ->get(route('service-providers.show', $this->serviceProvider));

        $response->assertStatus(200)
            ->assertSee($this->serviceProvider->company_name);
    }

    #[Test]
    public function test_guest_can_view_public_profile()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider));

        $response->assertStatus(200)
            ->assertSee($this->serviceProvider->company_name);
    }

    #[Test]
    public function test_owner_can_update_profile()
    {
        $data = [
            'business_name' => 'Updated Business Name',
            'phone' => '9876543210',
            'whatsapp_country_code' => '+1',
            'whatsapp_number' => '+1234567890',
            'bio' => 'Updated bio text',
            'experience_years' => 10,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.profile.update', $this->serviceProvider), $data);

        $response->assertRedirect(route('service-providers.show', $this->serviceProvider));

        $this->serviceProvider->refresh();
        $this->assertEquals('Updated Business Name', $this->serviceProvider->company_name);
        $this->assertEquals('9876543210', $this->serviceProvider->phone);
        // whatsapp_country_code (+1) is prepended to the submitted number.
        $this->assertEquals('+11234567890', $this->serviceProvider->whatsapp_number);
        $this->assertEquals('Updated bio text', $this->serviceProvider->bio);
        $this->assertEquals(10, $this->serviceProvider->experience_years);
    }

    #[Test]
    public function test_whatsapp_number_validation()
    {
        $data = [
            'business_name' => 'Test Business',
            'whatsapp_number' => 'invalid-format!!',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.profile.update', $this->serviceProvider), $data);

        $response->assertSessionHasErrors('whatsapp_number');
    }

    #[Test]
    public function test_category_can_be_changed_only_when_currently_others()
    {
        // Category is locked unless the current category is "Others".
        $others = Category::factory()->create(['name' => 'Others', 'is_active' => true]);
        $this->serviceProvider->update(['category_id' => $others->id]);

        $newCategory = Category::factory()->create([
            'name' => 'Electrical',
            'is_active' => true,
        ]);

        $data = [
            'business_name' => $this->serviceProvider->company_name,
            'phone' => '9876543210',
            'whatsapp_country_code' => '+1',
            'whatsapp_number' => '+1234567890',
            'category_id' => $newCategory->id,
        ];

        $this->actingAs($this->user)
            ->put(route('service-providers.profile.update', $this->serviceProvider), $data)
            ->assertRedirect();

        $this->serviceProvider->refresh();
        $this->assertEquals($newCategory->id, $this->serviceProvider->category_id);
    }

    #[Test]
    public function test_services_offered_persists_as_json()
    {
        $data = [
            'business_name' => $this->serviceProvider->company_name,
            'phone' => '9876543210',
            'whatsapp_country_code' => '+1',
            'whatsapp_number' => '+1234567890',
            'services_offered' => 'Plumbing, Repairs, Installation, Maintenance',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.profile.update', $this->serviceProvider), $data);

        $response->assertRedirect();
        $this->serviceProvider->refresh();
        $services = $this->serviceProvider->services_offered;
        $this->assertIsArray($services);
        $this->assertContains('Plumbing', $services);
        $this->assertContains('Repairs', $services);
    }

    #[Test]
    public function test_validation_errors_show_notification()
    {
        $data = [
            'business_name' => '', // Required field empty
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.profile.update', $this->serviceProvider), $data);

        $response->assertSessionHasErrors('business_name');
    }

    #[Test]
    public function test_database_transaction_rollback_on_error()
    {
        $originalName = $this->serviceProvider->company_name;

        // Simulate error by providing invalid data that passes validation but fails on save
        $data = [
            'business_name' => 'New Name',
            'category_id' => 99999, // Non-existent category
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.profile.update', $this->serviceProvider), $data);

        // Should redirect back with error
        $response->assertRedirect();

        // Database should not be updated (transaction rolled back)
        $this->serviceProvider->refresh();
        $this->assertEquals($originalName, $this->serviceProvider->company_name);
    }

    #[Test]
    public function test_unauthorized_user_cannot_update_profile()
    {
        $data = [
            'business_name' => 'Hacked Name',
        ];

        $response = $this->actingAs($this->otherUser)
            ->put(route('service-providers.profile.update', $this->serviceProvider), $data);

        $response->assertForbidden();

        $this->serviceProvider->refresh();
        $this->assertNotEquals('Hacked Name', $this->serviceProvider->company_name);
    }
}
