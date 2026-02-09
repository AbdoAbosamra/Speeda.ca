<?php

namespace Tests\Feature;

use App\Models\Endorsement;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndorsementTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $serviceProviderUser;
    private ServiceProvider $serviceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client user
        $this->client = User::factory()->create([
            'role' => 'client',
        ]);

        // Create a service provider user and their provider profile
        $this->serviceProviderUser = User::factory()->create([
            'role' => 'service_provider',
        ]);

        $this->serviceProvider = ServiceProvider::factory()->create([
            'user_id' => $this->serviceProviderUser->id,
            'endorsement_count' => 0,
        ]);
    }

    /**
     * Test client can endorse a service provider
     */
    public function test_client_can_endorse_service_provider(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'endorsed' => true,
                'count' => 1,
            ]);

        // Verify database
        $this->assertDatabaseHas('endorsements', [
            'service_provider_id' => $this->serviceProvider->id,
            'user_id' => $this->client->id,
        ]);

        // Verify count updated
        $this->serviceProvider->refresh();
        $this->assertEquals(1, $this->serviceProvider->endorsement_count);
    }

    /**
     * Test client can remove endorsement (un-endorse)
     */
    public function test_client_can_remove_endorsement(): void
    {
        // First create an endorsement
        Endorsement::create([
            'service_provider_id' => $this->serviceProvider->id,
            'user_id' => $this->client->id,
        ]);
        $this->serviceProvider->increment('endorsement_count');

        // Now toggle to remove
        $response = $this->actingAs($this->client)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'endorsed' => false,
                'count' => 0,
            ]);

        // Verify database
        $this->assertDatabaseMissing('endorsements', [
            'service_provider_id' => $this->serviceProvider->id,
            'user_id' => $this->client->id,
        ]);
    }

    /**
     * Test non-client cannot endorse
     */
    public function test_service_provider_cannot_endorse(): void
    {
        $response = $this->actingAs($this->serviceProviderUser)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test unauthenticated user cannot endorse
     */
    public function test_unauthenticated_user_cannot_endorse(): void
    {
        $response = $this->postJson(route('service-providers.endorse', $this->serviceProvider));

        $response->assertStatus(401);
    }

    /**
     * Test user cannot endorse same provider twice (unique constraint)
     */
    public function test_endorsement_creates_only_one_record(): void
    {
        // First endorsement
        $this->actingAs($this->client)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        // Second call should remove instead of creating duplicate
        $this->actingAs($this->client)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        // Third call should add again
        $this->actingAs($this->client)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        // Only one endorsement should exist
        $this->assertEquals(1, Endorsement::where([
            'service_provider_id' => $this->serviceProvider->id,
            'user_id' => $this->client->id,
        ])->count());
    }

    /**
     * Test user cannot endorse themselves
     */
    public function test_user_cannot_endorse_themselves(): void
    {
        // Create a client who also owns a service provider
        $clientWithProvider = User::factory()->create(['role' => 'client']);
        $ownProvider = ServiceProvider::factory()->create([
            'user_id' => $clientWithProvider->id,
            'endorsement_count' => 0,
        ]);

        $response = $this->actingAs($clientWithProvider)
            ->postJson(route('service-providers.endorse', $ownProvider));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test endorsement count updates correctly with multiple users
     */
    public function test_endorsement_count_updates_correctly(): void
    {
        $client2 = User::factory()->create(['role' => 'client']);
        $client3 = User::factory()->create(['role' => 'client']);

        // Three clients endorse
        $this->actingAs($this->client)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        $this->actingAs($client2)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        $this->actingAs($client3)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        $this->serviceProvider->refresh();
        $this->assertEquals(3, $this->serviceProvider->endorsement_count);

        // One removes their endorsement
        $this->actingAs($client2)
            ->postJson(route('service-providers.endorse', $this->serviceProvider));

        $this->serviceProvider->refresh();
        $this->assertEquals(2, $this->serviceProvider->endorsement_count);
    }
}
