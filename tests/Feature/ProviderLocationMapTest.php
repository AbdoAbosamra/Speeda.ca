<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProviderLocationMapTest extends TestCase
{
    use RefreshDatabase;

    private function provider(array $attributes = []): ServiceProvider
    {
        $location = Location::firstOrCreate(['city' => 'Toronto'], ['country' => 'Canada', 'is_active' => true]);
        $location->update(['is_active' => true]);

        return ServiceProvider::factory()->create(array_merge([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true])->id,
            'category_id' => Category::factory(),
            'location_id' => $location->id,
            'company_name' => 'Map Test Business',
            'address' => '220 Yonge Street, Toronto, ON M5B 2H1, Canada',
            'phone' => '+14165550101',
            'whatsapp_number' => '+14385550101',
            'is_active' => true,
        ], $attributes));
    }

    private function mapParameters(string $html): array
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($document);
        $frame = $xpath->query('//section[@id="provider-location"]//iframe')->item(0);
        $this->assertNotNull($frame, 'The public location card must contain an embedded map.');
        $url = parse_url($frame->getAttribute('src'));
        $this->assertSame('https', $url['scheme']);
        $this->assertSame('maps.google.com', $url['host']);
        parse_str($url['query'], $parameters);

        return $parameters;
    }

    public function test_visitors_see_the_full_address_and_map_without_revealing_contact_details(): void
    {
        $provider = $this->provider();

        $response = $this->get(route('service-providers.show', $provider))->assertOk()
            ->assertSee($provider->address)
            ->assertSee('id="provider-public-address"', false)
            ->assertSee('data-copy-provider-address', false)
            ->assertSee('https://www.google.com/maps/dir/?api=1&amp;destination='.rawurlencode($provider->address), false)
            ->assertSee('loading="lazy"', false)
            ->assertDontSee('id="addressText"', false);

        $parameters = $this->mapParameters($response->getContent());
        $this->assertSame($provider->address, $parameters['q']);
        $this->assertSame('embed', $parameters['output']);
    }

    public function test_a_profile_without_an_address_has_no_map_card(): void
    {
        $provider = $this->provider(['address' => null]);
        foreach ([null, '   '] as $address) {
            $provider->update(['address' => $address]);
            $this->get(route('service-providers.show', $provider))->assertOk()
                ->assertDontSee('id="provider-location"', false)
                ->assertDontSee('maps.google.com/maps?', false);
        }
    }

    public function test_private_locations_do_not_expose_the_address_or_map_to_visitors(): void
    {
        $provider = $this->provider(['address' => '51 Private Street, Private Town']);
        $provider->location->update(['is_active' => false]);

        foreach ([null, User::factory()->client()->create(['is_active' => true])] as $viewer) {
            if ($viewer) {
                $this->actingAs($viewer);
            }
            $this->get(route('service-providers.show', $provider))->assertOk()
                ->assertDontSee('id="provider-location"', false)
                ->assertDontSee($provider->address)
                ->assertDontSee(rawurlencode($provider->address), false);
        }
    }

    public function test_the_owner_can_still_preview_their_private_location(): void
    {
        $provider = $this->provider();
        $provider->location->update(['is_active' => false]);

        $response = $this->actingAs($provider->user)
            ->get(route('service-providers.show', $provider))->assertOk();

        $this->assertSame($provider->address, $this->mapParameters($response->getContent())['q']);
    }

    public function test_address_changes_are_saved_and_used_by_the_public_map_in_each_language(): void
    {
        $provider = $this->provider(['address' => null]);
        $addresses = [
            'ar' => '١٢ شارع التحرير، القاهرة، مصر',
            'en' => '220 Yonge Street, Toronto, Canada',
            'fr' => '123 rue Saint-José, Montréal, Canada',
        ];

        foreach ($addresses as $locale => $address) {
            $this->actingAs($provider->user)->put(route('service-providers.profile.update', $provider), [
                'business_name' => $provider->company_name,
                'phone' => $provider->phone,
                'whatsapp_country_code' => '+1',
                'whatsapp_number' => '4385550101',
                'address' => $address,
            ])->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame($address, $provider->fresh()->address);
            Auth::logout();

            $html = $this->get(route('service-providers.show', [$provider, 'lang' => $locale]))
                ->assertOk()->assertSee($address)->getContent();
            $parameters = $this->mapParameters($html);
            $this->assertSame($address, $parameters['q']);
            $this->assertSame($locale, $parameters['hl']);
        }
    }

    public function test_an_owner_can_clear_the_address_and_remove_the_public_map(): void
    {
        $provider = $this->provider();

        $this->actingAs($provider->user)->put(route('service-providers.profile.update', $provider), [
            'business_name' => $provider->company_name,
            'phone' => $provider->phone,
            'whatsapp_country_code' => '+1',
            'whatsapp_number' => '4385550101',
            'address' => '',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertNull($provider->fresh()->address);
        Auth::logout();
        $this->get(route('service-providers.show', $provider))->assertOk()
            ->assertDontSee('id="provider-location"', false);
    }

    public function test_an_address_cannot_inject_markup_or_change_embed_parameters(): void
    {
        $provider = $this->provider(['address' => '12 Main St & output=other "><script>alert(1)</script>']);

        $response = $this->get(route('service-providers.show', $provider))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);

        $parameters = $this->mapParameters($response->getContent());
        $this->assertSame($provider->address, $parameters['q']);
        $this->assertSame('embed', $parameters['output']);
    }
}
