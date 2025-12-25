<?php

namespace Tests\Feature;

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

    /** @test */
    public function test_owner_can_view_own_profile()
    {
        $response = $this->actingAs($this->user)
            ->get(route('service-providers.show', $this->serviceProvider));

        $response->assertStatus(200)
            ->assertSee($this->serviceProvider->company_name);
    }

    /** @test */
    public function test_non_owner_cannot_view_profile()
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('service-providers.show', $this->serviceProvider));

        $response->assertStatus(200)
            ->assertSee('Access Denied');
    }

    /** @test */
    public function test_guest_redirected_to_login()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_owner_can_update_profile()
    {
        $data = [
            'business_name' => 'Updated Business Name',
            'phone' => '9876543210',
            'whatsapp_number' => '+1234567890',
            'bio' => 'Updated bio text',
            'experience_years' => 10,
            'hourly_rate' => 75.50,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        $response->assertRedirect(route('service-providers.show', $this->serviceProvider));

        $this->serviceProvider->refresh();
        $this->assertEquals('Updated Business Name', $this->serviceProvider->company_name);
        $this->assertEquals('9876543210', $this->serviceProvider->phone);
        $this->assertEquals('+1234567890', $this->serviceProvider->whatsapp_number);
        $this->assertEquals('Updated bio text', $this->serviceProvider->bio);
        $this->assertEquals(10, $this->serviceProvider->experience_years);
        $this->assertEquals(75.50, $this->serviceProvider->hourly_rate);
    }

    /** @test */
    public function test_whatsapp_number_validation()
    {
        $data = [
            'business_name' => 'Test Business',
            'whatsapp_number' => 'invalid-format!!',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        $response->assertSessionHasErrors('whatsapp_number');
    }

    /** @test */
    public function test_profile_image_upload()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('profile.jpg', 800, 800);

        $data = [
            'business_name' => $this->serviceProvider->company_name,
            'profile_image' => $file,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        $response->assertRedirect();
        $this->serviceProvider->refresh();
        $this->assertNotNull($this->serviceProvider->profile_image);
        Storage::disk('public')->assertExists($this->serviceProvider->profile_image);
    }

    /** @test */
    public function test_certification_upload_pdf()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('certificate.pdf', 1000, 'application/pdf');

        $data = [
            'business_name' => $this->serviceProvider->company_name,
            'certification' => $file,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        $response->assertRedirect();
        $this->serviceProvider->refresh();
        $this->assertNotNull($this->serviceProvider->certification);
        $this->assertTrue($this->serviceProvider->is_certified);
        Storage::disk('public')->assertExists($this->serviceProvider->certification);
    }

    /** @test */
    public function test_certification_upload_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('certificate.jpg');

        $data = [
            'business_name' => $this->serviceProvider->company_name,
            'certification' => $file,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        $response->assertRedirect();
        $this->serviceProvider->refresh();
        $this->assertNotNull($this->serviceProvider->certification);
        $this->assertTrue($this->serviceProvider->is_certified);
    }

    /** @test */
    public function test_category_update()
    {
        $newCategory = Category::factory()->create([
            'name' => 'Electrical',
            'parent_id' => 1,
            'is_active' => true,
        ]);

        $data = [
            'business_name' => $this->serviceProvider->company_name,
            'category_id' => $newCategory->id,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        $response->assertRedirect();
        $this->serviceProvider->refresh();
        $this->assertEquals($newCategory->id, $this->serviceProvider->category_id);
    }

    /** @test */
    public function test_services_offered_persists_as_json()
    {
        $data = [
            'business_name' => $this->serviceProvider->company_name,
            'services_offered' => 'Plumbing, Repairs, Installation, Maintenance',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        $response->assertRedirect();
        $this->serviceProvider->refresh();
        $services = $this->serviceProvider->services_offered;
        $this->assertIsArray($services);
        $this->assertContains('Plumbing', $services);
        $this->assertContains('Repairs', $services);
    }

    /** @test */
    public function test_validation_errors_show_notification()
    {
        $data = [
            'business_name' => '', // Required field empty
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        $response->assertSessionHasErrors('business_name');
    }

    /** @test */
    public function test_database_transaction_rollback_on_error()
    {
        $originalName = $this->serviceProvider->company_name;

        // Simulate error by providing invalid data that passes validation but fails on save
        $data = [
            'business_name' => 'New Name',
            'category_id' => 99999, // Non-existent category
        ];

        $response = $this->actingAs($this->user)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        // Should redirect back with error
        $response->assertRedirect();

        // Database should not be updated (transaction rolled back)
        $this->serviceProvider->refresh();
        $this->assertEquals($originalName, $this->serviceProvider->company_name);
    }

    /** @test */
    public function test_parent_categories_only_shown_in_dropdown()
    {
        // Create child category (should not appear)
        Category::factory()->create([
            'name' => 'Child Category',
            'parent_id' => 2, // Not 1
            'is_active' => true,
        ]);

        // Create parent category (should appear)
        Category::factory()->create([
            'name' => 'Parent Category',
            'parent_id' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('service-providers.show', $this->serviceProvider));

        $response->assertStatus(200)
            ->assertSee('Parent Category')
            ->assertDontSee('Child Category');
    }

    /** @test */
    public function test_unauthorized_user_cannot_update_profile()
    {
        $data = [
            'business_name' => 'Hacked Name',
        ];

        $response = $this->actingAs($this->otherUser)
            ->put(route('service-providers.update', $this->serviceProvider), $data);

        $response->assertForbidden();

        $this->serviceProvider->refresh();
        $this->assertNotEquals('Hacked Name', $this->serviceProvider->company_name);
    }
}
