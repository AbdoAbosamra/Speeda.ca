<?php

namespace Tests\Unit\Models;

use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProviderProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_created_with_valid_data()
    {
        $user = User::factory()->create(['role' => 'service_provider']);

        $profile = ServiceProviderProfile::factory()->create([
            'user_id' => $user->id,
            'profession' => 'Home Services',
            'bio' => 'Professional cleaning and maintenance services',
            'experience_years' => 5,
            'hourly_rate' => 75.00
        ]);

        $this->assertInstanceOf(ServiceProviderProfile::class, $profile);
        $this->assertEquals('Home Services', $profile->profession);
        $this->assertEquals('Professional cleaning and maintenance services', $profile->bio);
        $this->assertEquals(5, $profile->experience_years);
        $this->assertEquals(75.00, $profile->hourly_rate);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create(['role' => 'service_provider']);
        $profile = ServiceProviderProfile::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertTrue($profile->user->is($user));
        $this->assertInstanceOf(User::class, $profile->user);
    }

    /** @test */
    public function it_validates_contact_information()
    {
        $profile = ServiceProviderProfile::factory()->create([
            'phone' => '+1-514-555-0123'
        ]);

        $this->assertEquals('+1-514-555-0123', $profile->phone);
    }

    /** @test */
    public function it_handles_social_media_links()
    {
        $profile = ServiceProviderProfile::factory()->create([
            'facebook' => 'https://facebook.com/homeservices',
            'instagram' => 'https://instagram.com/homeservices',
            'linkedin' => 'https://linkedin.com/company/homeservices'
        ]);

        $this->assertEquals('https://facebook.com/homeservices', $profile->facebook);
        $this->assertEquals('https://instagram.com/homeservices', $profile->instagram);
        $this->assertEquals('https://linkedin.com/company/homeservices', $profile->linkedin);
    }

    /** @test */
    public function it_validates_business_information()
    {
        $profile = ServiceProviderProfile::factory()->create([
            'business_type' => 'company',
            'company_name' => 'Premium Home Services',
            'is_verified' => true,
            'emergency_available' => true
        ]);

        $this->assertEquals('company', $profile->business_type);
        $this->assertEquals('Premium Home Services', $profile->company_name);
        $this->assertTrue($profile->is_verified);
        $this->assertTrue($profile->emergency_available);
    }

    /** @test */
    public function factory_creates_valid_profiles()
    {
        $profiles = ServiceProviderProfile::factory()->count(3)->create();

        $this->assertCount(3, $profiles);

        foreach ($profiles as $profile) {
            $this->assertNotNull($profile->user_id);
            $this->assertIsNumeric($profile->user_id);
            $this->assertContains($profile->business_type, ['individual', 'company']);
            // average_rating can be null or numeric
            $this->assertTrue($profile->average_rating === null || is_numeric($profile->average_rating));
            $this->assertIsInt($profile->completed_jobs);
        }
    }
}
