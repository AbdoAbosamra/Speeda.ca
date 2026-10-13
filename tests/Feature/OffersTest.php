<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\OfferSection;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Local Offers: the admin owns every offer, visitors see only live ones, and
 * "Get this offer on WhatsApp" opens a chat whose message names the SPEEDA
 * offer and links to it — no code and no limit.
 */
class OffersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private ServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // A browser keeps its session cookie between requests; the test client
        // does not, so pin one. Claims and view de-duplication key on it.
        $this->withCookie(config('session.cookie'), str_repeat('a', 40))->withCredentials();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->provider = $this->makeProvider('DRIVISA', '+16135550123');
    }

    private function makeProvider(string $name, ?string $whatsapp): ServiceProvider
    {
        $user = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);

        return ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'company_name' => $name,
            'whatsapp_number' => $whatsapp,
            'phone' => $whatsapp,
            'is_active' => true,
        ]);
    }

    private function makeOffer(array $attrs = []): Offer
    {
        $provider = $attrs['provider'] ?? $this->provider;
        unset($attrs['provider']);

        return Offer::create(array_merge([
            'service_provider_id' => $provider->id,
            'slug' => 'offer-' . uniqid(),
            'deal_type' => Offer::TYPE_PERCENT,
            'deal_value' => 10,
            'title_en' => 'New Student Special',
            'title_ar' => 'عرض الطالب الجديد',
            'summary_en' => 'Save 10% on driving lessons for new students.',
            'cta_type' => Offer::CTA_WHATSAPP,
            'starts_at' => now()->subDay()->toDateString(),
            'ends_at' => now()->addDays(20)->toDateString(),
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ], $attrs));
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'service_provider_id' => $this->provider->id,
            'deal_type' => 'percent',
            'deal_value' => 15,
            'title_en' => '15% Off Any Service',
            'summary_en' => 'Get 15% off your next service.',
            'cta_type' => 'whatsapp',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => 1,
            'is_featured' => 1,
        ], $overrides);
    }

    /* ================================================================== */
    /*  Status & visibility                                                */
    /* ================================================================== */

    public function test_status_is_derived_from_the_switch_and_the_dates(): void
    {
        $this->assertSame('live', $this->makeOffer()->status());
        $this->assertSame('draft', $this->makeOffer(['is_active' => false])->status());
        $this->assertSame('scheduled', $this->makeOffer(['starts_at' => now()->addDays(3)->toDateString()])->status());
        $this->assertSame('expired', $this->makeOffer(['ends_at' => now()->subDay()->toDateString()])->status());
        // The last day is still live.
        $this->assertSame('live', $this->makeOffer(['ends_at' => now()->toDateString()])->status());

        $this->assertSame(2, Offer::live()->count());
        $this->assertSame(1, Offer::query()->withStatus('scheduled')->count());
        $this->assertSame(1, Offer::query()->withStatus('expired')->count());
        $this->assertSame(1, Offer::query()->withStatus('draft')->count());
    }

    public function test_offers_of_a_hidden_provider_are_not_live(): void
    {
        $this->makeOffer();
        $this->provider->update(['is_active' => false]);

        $this->assertSame(0, Offer::live()->count());
    }

    public function test_badge_label_follows_the_deal_type_and_locale(): void
    {
        $this->assertSame('10% OFF', $this->makeOffer()->badgeLabel('en'));
        $this->assertSame('$30 OFF', $this->makeOffer(['deal_type' => 'fixed', 'deal_value' => 30])->badgeLabel('en'));
        $this->assertSame('FREE', $this->makeOffer(['deal_type' => 'free', 'deal_value' => null])->badgeLabel('en'));
        $this->assertSame('مجانًا', $this->makeOffer(['deal_type' => 'free', 'deal_value' => null])->badgeLabel('ar'));
        $custom = $this->makeOffer(['deal_type' => 'custom', 'deal_value' => null, 'badge_text_en' => '2 WEEKS FREE']);
        $this->assertSame('2 WEEKS FREE', $custom->badgeLabel('en'));
        // Arabic was left empty, so the English badge is used.
        $this->assertSame('2 WEEKS FREE', $custom->badgeLabel('ar'));
    }

    /* ================================================================== */
    /*  Home page                                                          */
    /* ================================================================== */

    public function test_home_section_is_absent_without_a_featured_live_offer(): void
    {
        $this->makeOffer(['is_featured' => false]);
        $this->makeOffer(['is_active' => false]);

        $this->get(route('home'))->assertOk()->assertDontSee('id="local-offers"', false);
    }

    public function test_home_section_shows_featured_live_offers_above_explore_services(): void
    {
        $this->makeOffer(['title_en' => 'Visible Deal']);

        $html = $this->get(route('home'))->assertOk()->assertSee('id="local-offers"', false)->assertSee('Visible Deal')->getContent();

        $this->assertLessThan(
            strpos($html, __('home.explore_services')),
            strpos($html, 'id="local-offers"'),
            'The offers section must come before Explore Services.'
        );
    }

    public function test_admin_can_switch_the_home_section_off(): void
    {
        $this->makeOffer();
        OfferSection::current()->update(['is_active' => false]);

        $this->get(route('home'))->assertOk()->assertDontSee('id="local-offers"', false);
    }

    public function test_home_section_respects_the_card_limit(): void
    {
        foreach (range(1, 6) as $i) {
            $this->makeOffer(['title_en' => "Deal {$i}", 'sort_order' => $i]);
        }
        OfferSection::current()->update(['max_items' => 4]);

        $this->assertCount(4, Offer::forHomePage());
    }

    /* ================================================================== */
    /*  Public pages                                                       */
    /* ================================================================== */

    public function test_offers_page_lists_all_live_offers_without_filters(): void
    {
        $driving = $this->makeOffer(['title_en' => 'Driving Deal', 'sort_order' => 1]);
        $cleaning = $this->makeOffer(['title_en' => 'Cleaning Deal', 'sort_order' => 2, 'ends_at' => now()->addDay()->toDateString(), 'provider' => $this->makeProvider('SparkClean', '+16135550999')]);
        $this->makeOffer(['title_en' => 'Old Deal', 'ends_at' => now()->subDays(2)->toDateString()]);

        $this->get(route('offers.index'))->assertOk()
            ->assertSee('Driving Deal')->assertSee('Cleaning Deal')->assertDontSee('Old Deal')
            ->assertDontSee('id="so-city"', false)
            ->assertDontSee('id="so-sort"', false)
            ->assertDontSee('class="so-filters"', false);

        // Bookmarked filter links must still show every offer in admin order.
        $this->get(route('offers.index', [
            'q' => 'spark', 'city' => $cleaning->serviceProvider->location_id,
            'category' => 'nonexistent', 'sort' => 'ending',
        ]))->assertOk()
            ->assertSee('Driving Deal')->assertSee('Cleaning Deal')->assertDontSee('Old Deal')
            ->assertViewHas('offers', fn ($offers) => $offers->pluck('id')->all() === [$driving->id, $cleaning->id])
            ->assertViewHas('endingSoon', fn ($offers) => $offers->contains('id', $cleaning->id));
    }

    public function test_offers_pagination_keeps_all_cities_and_drops_old_filter_parameters(): void
    {
        $expected = [];
        for ($i = 1; $i <= 13; $i++) {
            $expected[] = $this->makeOffer(['title_en' => 'Paginated Offer ' . $i, 'sort_order' => $i])->id;
        }

        $query = ['q' => 'no match', 'city' => 999999, 'category' => 'missing', 'sort' => 'newest', 'lang' => 'fr'];
        $this->get(route('offers.index', $query))->assertOk()
            ->assertViewHas('offers', function ($offers) use ($expected) {
                $this->assertSame(13, $offers->total());
                $this->assertSame(array_slice($expected, 0, 12), $offers->pluck('id')->all());
                parse_str((string) parse_url($offers->nextPageUrl(), PHP_URL_QUERY), $nextQuery);
                $this->assertSame(['lang' => 'fr', 'page' => '2'], $nextQuery);

                return true;
            });

        $this->get(route('offers.index', array_merge($query, ['page' => 2])))->assertOk()
            ->assertViewHas('offers', fn ($offers) => $offers->pluck('id')->all() === [$expected[12]]);
    }

    public function test_offer_page_is_hidden_for_drafts_but_kept_for_expired_offers(): void
    {
        $draft = $this->makeOffer(['is_active' => false]);
        $expired = $this->makeOffer(['ends_at' => now()->subDays(2)->toDateString()]);
        $live = $this->makeOffer(['title_en' => 'Live One']);

        $this->get(route('offers.show', $draft->slug))->assertNotFound();
        $this->get(route('offers.show', $expired->slug))->assertOk()->assertSee(__('offers.ended_title'));
        $this->get(route('offers.show', $live->slug))->assertOk()->assertSee('Live One')->assertSee(__('offers.claim_whatsapp'));

        // The admin can preview a draft.
        $this->actingAs($this->admin)->get(route('offers.show', $draft->slug))->assertOk();
    }

    public function test_a_view_is_counted_once_per_session_with_its_source(): void
    {
        $offer = $this->makeOffer();

        $this->get(route('offers.show', ['slug' => $offer->slug, 'src' => 'home']))->assertOk();
        $this->get(route('offers.show', ['slug' => $offer->slug, 'src' => 'home']))->assertOk();

        $this->assertSame(1, OfferEvent::where('event', 'view')->count());
        $this->assertSame('home', OfferEvent::first()->source);
        $this->assertSame(1, $offer->fresh()->views_count);
    }

    public function test_admin_visits_are_not_counted(): void
    {
        $offer = $this->makeOffer();

        $this->actingAs($this->admin)->get(route('offers.show', $offer->slug))->assertOk();

        $this->assertSame(0, OfferEvent::count());
        $this->assertSame(0, $offer->fresh()->views_count);
    }

    public function test_provider_profile_shows_its_live_offers(): void
    {
        $this->makeOffer(['title_en' => 'Profile Deal']);

        $this->get(route('service-providers.show', $this->provider->id))->assertOk()
            ->assertSee('Profile Deal')
            ->assertSee(__('offers.profile_offers_title'));
    }

    public function test_the_message_is_written_in_the_visitors_language(): void
    {
        $offer = $this->makeOffer();

        $location = $this->get(route('offers.go', $offer->slug) . '?lang=ar')->headers->get('Location');

        app()->setLocale('ar');
        $this->assertStringContainsString('سبيدا', rawurldecode($location));
    }

    public function test_ended_offers_send_visitors_back_to_the_offer_page(): void
    {
        $offer = $this->makeOffer(['ends_at' => now()->subDay()->toDateString()]);

        $this->get(route('offers.go', $offer->slug))->assertRedirect(route('offers.show', $offer->slug));
        $this->assertSame(0, $offer->fresh()->claims_count);
    }

    public function test_without_whatsapp_the_button_falls_back_to_the_profile(): void
    {
        $provider = $this->makeProvider('No Phone Co', null);
        $offer = $this->makeOffer(['provider' => $provider]);

        $this->get(route('offers.go', $offer->slug))->assertRedirect(route('service-providers.show', $provider->id));
    }

    /* ================================================================== */
    /*  "Get this offer on WhatsApp" — no code, no limit                   */
    /* ================================================================== */

    public function test_the_whatsapp_message_says_the_customer_came_from_a_speeda_offer(): void
    {
        $offer = $this->makeOffer();

        $location = $this->get(route('offers.go', ['slug' => $offer->slug, 'src' => 'home']))
            ->assertRedirect()
            ->headers->get('Location');

        $this->assertStringStartsWith('https://wa.me/16135550123?text=', $location);
        $message = rawurldecode(substr($location, strpos($location, '?text=') + 6));
        $this->assertStringContainsString('SPEEDA', $message);
        $this->assertStringContainsString('New Student Special', $message);
        $this->assertStringContainsString('10% OFF', $message);
        $this->assertStringContainsString(route('offers.show', $offer->slug), $message);
        $this->assertStringNotContainsString('SPD-', $message);

        $this->assertSame(1, $offer->fresh()->claims_count);
        $this->assertSame('home', OfferEvent::where('event', 'claim')->value('source'));
    }

    public function test_the_offer_has_no_limit_and_a_repeat_click_is_not_a_second_lead(): void
    {
        $offer = $this->makeOffer();

        foreach (range(1, 5) as $i) {
            $this->get(route('offers.go', $offer->slug))->assertRedirect();
        }

        // Every press opens WhatsApp; the same visitor counts once per 30 minutes.
        $this->assertSame(1, $offer->fresh()->claims_count);

        $this->withCookie(config('session.cookie'), str_repeat('z', 40));
        $this->get(route('offers.go', $offer->slug), ['User-Agent' => 'Another phone'])->assertRedirect();
        $this->assertSame(2, $offer->fresh()->claims_count);
    }

    public function test_admin_clicks_open_whatsapp_but_are_not_counted(): void
    {
        $offer = $this->makeOffer();

        $this->actingAs($this->admin)->get(route('offers.go', $offer->slug))->assertRedirect();

        $this->assertSame(0, $offer->fresh()->claims_count);
    }

    public function test_the_offer_page_has_a_plain_whatsapp_link_and_no_code(): void
    {
        $offer = $this->makeOffer();

        $this->get(route('offers.show', $offer->slug))
            ->assertOk()
            ->assertSee(route('offers.go', ['slug' => $offer->slug, 'src' => 'direct']), false)
            ->assertSee(__('offers.claim_whatsapp'))
            ->assertDontSee('SPD-', false);
    }

    /* ================================================================== */
    /*  Tracking endpoint                                                  */
    /* ================================================================== */

    public function test_impressions_are_recorded_once_per_offer_and_placement(): void
    {
        $a = $this->makeOffer();
        $b = $this->makeOffer();
        $draft = $this->makeOffer(['is_active' => false]);

        $payload = ['event' => 'impression', 'ids' => [$a->id, $b->id, $draft->id], 'source' => 'home'];
        $this->postJson(route('offers.track'), $payload)->assertOk()->assertJson(['recorded' => 2]);
        $this->postJson(route('offers.track'), $payload)->assertOk()->assertJson(['recorded' => 0]);

        $this->assertSame(2, OfferEvent::where('event', 'impression')->count());
    }

    /* ================================================================== */
    /*  Admin                                                              */
    /* ================================================================== */

    public function test_only_admins_reach_the_offer_dashboard(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);

        $this->actingAs($client)->get(route('admin.offers.index'))->assertStatus(403);
        $this->actingAs($this->provider->user)->post(route('admin.offers.store'), $this->validPayload())->assertStatus(403);
        $this->assertSame(0, Offer::count());
    }

    public function test_admin_pages_render(): void
    {
        $offer = $this->makeOffer();
        $this->get(route('offers.go', $offer->slug));

        $this->actingAs($this->admin);
        $this->get(route('admin.offers.index'))->assertOk()->assertSee('New Student Special');
        $this->get(route('admin.offers.create'))->assertOk();
        $this->get(route('admin.offers.edit', $offer))->assertOk();
        $this->get(route('admin.offers.analytics'))->assertOk()->assertSee('Offer statistics')->assertSee('WhatsApp clicks');
        $this->get(route('admin.offers.analytics', ['range' => 'custom', 'from' => now()->subDays(3)->toDateString(), 'to' => now()->toDateString(), 'offer_id' => $offer->id]))->assertOk();
    }

    public function test_admin_creates_an_offer_with_an_image_and_a_slug(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.offers.store'), $this->validPayload(['image' => UploadedFile::fake()->image('cover.jpg', 1600, 1000)]))
            ->assertRedirect();

        $offer = Offer::firstOrFail();
        $this->assertSame('drivisa-15-off-any-service', $offer->slug);
        $this->assertSame($this->admin->id, $offer->created_by);
        $this->assertNotNull($offer->image_path);
        Storage::disk('public')->assertExists($offer->image_path);
        $this->assertSame('live', $offer->status());
    }

    public function test_admin_can_choose_and_create_offers_for_providers_in_any_city(): void
    {
        $providers = collect(['Montreal', 'Toronto', 'Mississauga', 'Vancouver', 'Calgary', 'New Offer City'])
            ->map(function ($city, $index) {
                $location = Location::firstOrCreate(['city' => $city], ['country' => 'Canada', 'is_active' => true]);
                $provider = $this->makeProvider($city . ' Services', '+1613555020' . $index);
                $provider->update(['location_id' => $location->id]);

                return $provider;
            });

        $this->actingAs($this->admin)->get(route('admin.offers.create'))->assertOk()
            ->assertViewHas('providers', fn ($options) => $providers->pluck('id')->diff($options->pluck('id'))->isEmpty());

        foreach ($providers as $provider) {
            $title = 'Offer in ' . $provider->location->city;
            $this->post(route('admin.offers.store'), $this->validPayload([
                'service_provider_id' => $provider->id, 'title_en' => $title,
            ]))->assertSessionHasNoErrors()->assertRedirect();

            $this->assertDatabaseHas('offers', [
                'service_provider_id' => $provider->id, 'title_en' => $title, 'is_active' => true,
            ]);
        }

        $response = $this->get(route('offers.index'))->assertOk()
            ->assertViewHas('offers', fn ($offers) => $offers->total() === $providers->count());
        foreach ($providers as $provider) {
            $response->assertSee('Offer in ' . $provider->location->city);
        }
    }

    public function test_store_validates_the_deal(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.offers.store'), $this->validPayload(['deal_value' => 150, 'title_en' => '', 'ends_at' => now()->subYear()->toDateString()]))
            ->assertSessionHasErrors(['deal_value', 'title_en', 'ends_at']);

        $this->actingAs($this->admin)
            ->post(route('admin.offers.store'), $this->validPayload(['deal_type' => 'custom', 'deal_value' => null]))
            ->assertSessionHasErrors(['badge_text_en']);

        $this->actingAs($this->admin)
            ->post(route('admin.offers.store'), $this->validPayload(['cta_type' => 'url', 'cta_url' => 'javascript:alert(1)']))
            ->assertSessionHasErrors(['cta_url']);

        $this->assertSame(0, Offer::count());
    }

    public function test_editing_keeps_the_slug_so_shared_links_survive(): void
    {
        $offer = $this->makeOffer(['slug' => 'drivisa-new-student-special']);

        $this->actingAs($this->admin)
            ->put(route('admin.offers.update', $offer), $this->validPayload(['title_en' => 'A Better Title']))
            ->assertRedirect();

        $offer->refresh();
        $this->assertSame('A Better Title', $offer->title_en);
        $this->assertSame('drivisa-new-student-special', $offer->slug);
    }

    public function test_admin_quick_actions(): void
    {
        $offer = $this->makeOffer();
        $other = $this->makeOffer();
        $this->actingAs($this->admin);

        $this->patch(route('admin.offers.toggle', $offer))->assertRedirect();
        $this->assertFalse($offer->fresh()->is_active);

        $this->patch(route('admin.offers.feature', $offer))->assertRedirect();
        $this->assertFalse($offer->fresh()->is_featured);

        $this->postJson(route('admin.offers.reorder'), ['ids' => [$other->id, $offer->id]])->assertOk();
        $this->assertSame(1, $other->fresh()->sort_order);
        $this->assertSame(2, $offer->fresh()->sort_order);

        $this->post(route('admin.offers.duplicate', $offer))->assertRedirect();
        $copy = Offer::latest('id')->first();
        $this->assertFalse($copy->is_active);
        $this->assertNotSame($offer->slug, $copy->slug);

        $this->post(route('admin.offers.bulk'), ['bulk_action' => 'activate', 'ids' => [$offer->id, $copy->id]])->assertRedirect();
        $this->assertTrue($offer->fresh()->is_active);

        $this->delete(route('admin.offers.destroy', $offer))->assertRedirect();
        $this->assertSoftDeleted('offers', ['id' => $offer->id]);
    }

    public function test_admin_saves_the_home_section_settings(): void
    {
        $this->actingAs($this->admin)->put(route('admin.offers.section'), [
            'is_active' => 1,
            'title_en' => 'Hot Deals',
            'title_ar' => 'عروض ساخنة',
            'max_items' => 6,
        ])->assertRedirect();

        $section = OfferSection::current();
        $this->assertSame('Hot Deals', $section->title_en);
        $this->assertSame(6, $section->max_items);

        $this->makeOffer();
        $this->get(route('home'))->assertSee('Hot Deals');
    }

    public function test_analytics_funnel_numbers_add_up(): void
    {
        $offer = $this->makeOffer();
        $this->postJson(route('offers.track'), ['event' => 'impression', 'ids' => [$offer->id], 'source' => 'home']);
        $this->get(route('offers.show', ['slug' => $offer->slug, 'src' => 'home']));
        $this->get(route('offers.go', ['slug' => $offer->slug, 'src' => 'home']));

        $service = app(\App\Services\Admin\OfferAnalyticsService::class);
        $f = $service->filtersFromRequest(request());
        $summary = $service->summary($f)['current'];

        $this->assertSame(1, $summary['impressions']);
        $this->assertSame(1, $summary['views']);
        $this->assertSame(1, $summary['clicks']);
        $this->assertSame(100.0, $summary['ctr']);
        $this->assertSame(100.0, $summary['click_rate']);

        $row = $service->offerTable($f)->firstWhere('offer.id', $offer->id);
        $this->assertSame(1, $row['clicks']);
        $this->assertSame('home', $service->breakdown($f, 'source')[0]['label']);
    }

    public function test_sitemap_contains_only_live_public_offers(): void
    {
        $live = $this->makeOffer(['slug' => 'sitemap-live']);
        $this->makeOffer(['slug' => 'sitemap-draft', 'is_active' => false]);
        $this->makeOffer(['slug' => 'sitemap-scheduled', 'starts_at' => now()->addDays(3)]);
        $this->makeOffer(['slug' => 'sitemap-expired', 'ends_at' => now()->subDay()]);
        $this->makeOffer(['slug' => 'sitemap-deleted'])->delete();
        $hidden = $this->makeProvider('Hidden provider', null);
        $hidden->update(['is_active' => false]);
        $this->makeOffer(['slug' => 'sitemap-hidden', 'provider' => $hidden]);

        $publicPath = public_path();
        $temporary = storage_path('framework/testing/sitemap-'.uniqid());
        \Illuminate\Support\Facades\File::ensureDirectoryExists($temporary);
        try {
            $this->app->usePublicPath($temporary);
            app(\App\Domain\SEO\Services\SitemapService::class)->generate('https://speeda.ca');
            $xml = file_get_contents($temporary.'/sitemap.xml');
            $this->assertStringContainsString('https://speeda.ca/offers/'.$live->slug, $xml);
            foreach (['draft', 'scheduled', 'expired', 'deleted', 'hidden'] as $status) {
                $this->assertStringNotContainsString('sitemap-'.$status, $xml);
            }
        } finally {
            $this->app->usePublicPath($publicPath);
            \Illuminate\Support\Facades\File::deleteDirectory($temporary);
        }
    }

    public function test_robots_excludes_private_pages_and_offer_redirects(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));
        foreach (['Disallow: /admin', 'Disallow: /my-favorites', 'Disallow: /offers/*/whatsapp'] as $rule) {
            $this->assertStringContainsString($rule, $robots);
        }
    }

    public function test_offer_pagination_is_bounded_and_page_two_is_noindex(): void
    {
        foreach (range(1, 15) as $i) {
            $this->makeOffer(['sort_order' => $i]);
        }
        $this->get(route('offers.index'))->assertOk()->assertViewHas('offers', function ($offers) {
            return $offers->total() === 15 && $offers->count() === 12;
        });
        $this->get(route('offers.index', ['page' => 2]))->assertOk()
            ->assertSee('noindex, follow', false)
            ->assertViewHas('offers', fn ($offers) => $offers->count() === 3);
    }

    public function test_admin_review_is_explicit_and_is_not_copied(): void
    {
        $this->actingAs($this->admin)->post(route('admin.offers.store'), $this->validPayload(['mark_verified' => 1]))
            ->assertSessionHasNoErrors()->assertRedirect();
        $offer = Offer::latest('id')->firstOrFail();
        $this->assertNotNull($offer->verified_at);
        $this->assertEquals($this->admin->id, $offer->verified_by);
        $this->get(route('offers.show', $offer->slug))->assertSee('Reviewed by the SPEEDA team');

        $this->post(route('admin.offers.duplicate', $offer))->assertRedirect();
        $this->assertNull(Offer::latest('id')->first()->verified_at);
        $this->put(route('admin.offers.update', $offer), $this->validPayload(['mark_verified' => 0]))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($offer->fresh()->verified_at);
        $this->assertNull($offer->fresh()->verified_by);
        $this->get(route('offers.show', $offer->slug))->assertDontSee('Reviewed by the SPEEDA team');
    }

    public function test_review_date_only_moves_when_the_reviewed_facts_change(): void
    {
        $this->actingAs($this->admin)->post(route('admin.offers.store'), $this->validPayload(['mark_verified' => 1]))
            ->assertSessionHasNoErrors();
        $offer = Offer::latest('id')->firstOrFail();
        $reviewed = now()->subDays(10)->startOfSecond();
        $offer->forceFill(['verified_at' => $reviewed])->save();

        // Polishing the wording keeps the original review date.
        $this->put(route('admin.offers.update', $offer), $this->validPayload(['mark_verified' => 1, 'title_en' => 'Fifteen percent off']))
            ->assertSessionHasNoErrors();
        $this->assertTrue($offer->fresh()->verified_at->equalTo($reviewed));

        // A different deal is a new review.
        $this->put(route('admin.offers.update', $offer), $this->validPayload(['mark_verified' => 1, 'deal_value' => 20]))
            ->assertSessionHasNoErrors();
        $this->assertTrue($offer->fresh()->verified_at->gt($reviewed));

        $this->get(route('admin.offers.edit', $offer))->assertOk()->assertSee('name="mark_verified" value="1" checked', false);
    }

    public function test_dashboard_links_and_offer_metrics_are_available(): void
    {
        $this->makeOffer(['ends_at' => today()->addDays(3)]);
        $this->makeOffer(['is_active' => false]);
        $metrics = app(\App\Services\Admin\AdminDashboardService::class)->offerMetrics();
        $this->assertSame(1, $metrics['live']);
        $this->assertSame(1, $metrics['ending_soon']);
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee(route('admin.offers.index'), false)
            ->assertSee(route('admin.offers.comments'), false)
            ->assertSee('Offers Overview');
    }
}
