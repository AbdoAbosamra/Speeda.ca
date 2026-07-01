<?php

namespace Tests\Feature;

use App\Models\Endorsement;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Endorsement ("Recommend") feature.
 *
 * The endpoint is POST /service-providers/{serviceProvider}/endorse
 * (route name endorsements.toggle) and responds with redirects + flash
 * messages (not JSON). Only clients can endorse, and it toggles on repeat.
 */
class EndorsementTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $serviceProviderUser;
    private ServiceProvider $serviceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->client()->create();

        $this->serviceProviderUser = User::factory()->serviceProvider()->create();
        $this->serviceProvider = ServiceProvider::factory()->create([
            'user_id' => $this->serviceProviderUser->id,
            'endorsement_count' => 0,
        ]);
    }

    private function endorse(User $user, ServiceProvider $provider)
    {
        return $this->actingAs($user)
            ->from(route('service-providers.show', $provider->id))
            ->post(route('endorsements.toggle', $provider));
    }

    /** Test client can endorse a service provider */
    public function test_client_can_endorse_service_provider(): void
    {
        $this->endorse($this->client, $this->serviceProvider)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('endorsements', [
            'service_provider_id' => $this->serviceProvider->id,
            'user_id' => $this->client->id,
        ]);

        $this->serviceProvider->refresh();
        $this->assertEquals(1, $this->serviceProvider->endorsement_count);
    }

    /** Test client can remove endorsement (un-endorse) */
    public function test_client_can_remove_endorsement(): void
    {
        Endorsement::create([
            'service_provider_id' => $this->serviceProvider->id,
            'user_id' => $this->client->id,
        ]);
        $this->serviceProvider->increment('endorsement_count');

        $this->endorse($this->client, $this->serviceProvider)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('endorsements', [
            'service_provider_id' => $this->serviceProvider->id,
            'user_id' => $this->client->id,
        ]);
    }

    /** Test non-client cannot endorse */
    public function test_service_provider_cannot_endorse(): void
    {
        $this->endorse($this->serviceProviderUser, $this->serviceProvider)
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('endorsements', [
            'service_provider_id' => $this->serviceProvider->id,
            'user_id' => $this->serviceProviderUser->id,
        ]);
    }

    /** Test unauthenticated user cannot endorse */
    public function test_unauthenticated_user_cannot_endorse(): void
    {
        $this->post(route('endorsements.toggle', $this->serviceProvider))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('endorsements', 0);
    }

    /** Test toggling repeatedly keeps a single record */
    public function test_endorsement_creates_only_one_record(): void
    {
        $this->endorse($this->client, $this->serviceProvider); // create
        $this->endorse($this->client, $this->serviceProvider); // remove
        $this->endorse($this->client, $this->serviceProvider); // create again

        $this->assertEquals(1, Endorsement::where([
            'service_provider_id' => $this->serviceProvider->id,
            'user_id' => $this->client->id,
        ])->count());
    }

    /** Test user cannot endorse themselves */
    public function test_user_cannot_endorse_themselves(): void
    {
        $clientWithProvider = User::factory()->client()->create();
        $ownProvider = ServiceProvider::factory()->create([
            'user_id' => $clientWithProvider->id,
            'endorsement_count' => 0,
        ]);

        $this->endorse($clientWithProvider, $ownProvider)
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('endorsements', [
            'service_provider_id' => $ownProvider->id,
            'user_id' => $clientWithProvider->id,
        ]);
    }

    /** Test endorsement count updates correctly with multiple users */
    public function test_endorsement_count_updates_correctly(): void
    {
        $client2 = User::factory()->client()->create();
        $client3 = User::factory()->client()->create();

        $this->endorse($this->client, $this->serviceProvider);
        $this->endorse($client2, $this->serviceProvider);
        $this->endorse($client3, $this->serviceProvider);

        $this->serviceProvider->refresh();
        $this->assertEquals(3, $this->serviceProvider->endorsement_count);

        $this->endorse($client2, $this->serviceProvider);

        $this->serviceProvider->refresh();
        $this->assertEquals(2, $this->serviceProvider->endorsement_count);
    }
}
