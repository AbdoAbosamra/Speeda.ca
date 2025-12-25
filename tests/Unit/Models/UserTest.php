<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderProfile;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 🧪 User Model Unit Tests
 *
 * Testing all User model functionality, relationships, and business logic
 * Priority: ⭐⭐⭐⭐⭐ (Critical)
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_created_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'client'
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('client', $user->role);
        $this->assertTrue(\Hash::check('password123', $user->password));
    }

    /** @test */
    public function password_is_automatically_hashed()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plaintext',
            'role' => 'client'
        ]);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(\Hash::check('plaintext', $user->password));
    }

    /** @test */
    public function it_has_service_provider_profile_relationship()
    {
        $user = User::factory()->create(['role' => 'service_provider']);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $user->serviceProviderProfile()
        );
    }

    /** @test */
    public function it_has_service_provider_relationship()
    {
        $user = User::factory()->create(['role' => 'service_provider']);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $user->serviceProvider()
        );
    }

    /** @test */
    public function it_has_bookings_relationship()
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $user->bookings()
        );
    }

    /** @test */
    public function client_can_have_multiple_bookings()
    {
        $client = User::factory()->create(['role' => 'client']);
        $serviceProvider = User::factory()->create(['role' => 'service_provider']);

        // Create ServiceProvider record for the user
        $sp = ServiceProvider::factory()->create(['user_id' => $serviceProvider->id]);

        // Create bookings
        Booking::factory()->count(3)->create([
            'client_id' => $client->id,
            'service_provider_id' => $sp->id
        ]);

        $this->assertCount(3, $client->bookings);
    }

    /** @test */
    public function service_provider_user_can_create_profile()
    {
        $user = User::factory()->create(['role' => 'service_provider']);

        $profile = ServiceProviderProfile::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($profile->id, $user->serviceProviderProfile->id);
    }

    /** @test */
    public function it_hidden_sensitive_attributes()
    {
        $user = User::factory()->create();
        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    /** @test */
    public function email_verified_at_is_cast_to_datetime()
    {
        $user = User::factory()->create([
            'email_verified_at' => '2023-01-01 12:00:00'
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }

    /** @test */
    public function it_requires_name()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
    }

    /** @test */
    public function it_requires_unique_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'name' => 'Test User 2',
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
    }

    /** @test */
    public function it_can_check_if_user_is_service_provider()
    {
        $serviceProvider = User::factory()->create(['role' => 'service_provider']);
        $client = User::factory()->create(['role' => 'client']);

        $this->assertEquals('service_provider', $serviceProvider->role);
        $this->assertEquals('client', $client->role);
    }

    /** @test */
    public function it_can_access_fillable_attributes()
    {
        $user = new User();

        $expectedFillable = [
            'name',
            'email',
            'password',
            'profession',
            'role'
        ];

        $this->assertEquals($expectedFillable, $user->getFillable());
    }
}
