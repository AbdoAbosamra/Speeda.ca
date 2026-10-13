<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * What search engines and share previews read from each public page.
 */
class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    private function provider(array $attrs = []): ServiceProvider
    {
        return ServiceProvider::factory()->create(array_merge([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true])->id,
            'company_name' => 'Maple Plumbing',
            'is_active' => true,
        ], $attrs));
    }

    private function offer(array $attrs = []): Offer
    {
        return Offer::create(array_merge([
            'service_provider_id' => $attrs['service_provider_id'] ?? $this->provider()->id,
            'slug' => 'maple-spring-deal',
            'deal_type' => Offer::TYPE_PERCENT,
            'deal_value' => 15,
            'title_en' => 'Spring Deal',
            'summary_en' => 'Save 15% on any visit.',
            'cta_type' => Offer::CTA_PROFILE,
            'starts_at' => now()->subDay()->toDateString(),
            'ends_at' => now()->addDays(10)->toDateString(),
            'is_active' => true,
            'sort_order' => 1,
        ], $attrs));
    }

    public function test_a_search_term_cannot_break_out_of_the_json_ld_script(): void
    {
        $payload = '</script><script>alert(document.domain)</script>';

        $this->get(route('service-providers.index', ['search' => $payload]))
            ->assertOk()
            ->assertDontSee('<script>alert(document.domain)', false);
    }

    public function test_each_search_gets_its_own_title(): void
    {
        $this->get(route('service-providers.index', ['search' => 'plumber']));

        $html = $this->get(route('service-providers.index', ['search' => 'electrician']))->getContent();

        $this->assertStringContainsString('<title>"electrician" | ' . config('app.name') . '</title>', $html);
        $this->assertStringNotContainsString('"plumber"', $html);
    }

    public function test_categories_and_the_provider_list_keep_their_own_title_and_canonical(): void
    {
        // Whichever is visited first must not decide the other's meta.
        $this->get(route('service-providers.index'));

        $this->get(route('categories'))
            ->assertSee('<title>' . __('seo.categories_all') . ' | ' . config('app.name') . '</title>', false)
            ->assertSee('<link rel="canonical" href="' . route('categories') . '"', false);

        $this->get(route('service-providers.index'))
            ->assertSee('<title>' . __('seo.providers_all') . ' | ' . config('app.name') . '</title>', false)
            ->assertSee('<link rel="canonical" href="' . route('service-providers.index') . '"', false);
    }

    public function test_pages_carry_exactly_one_robots_tag(): void
    {
        $provider = $this->provider();
        foreach (range(1, 13) as $i) {
            $this->offer(['slug' => 'deal-' . $i, 'service_provider_id' => $provider->id]);
        }

        $home = $this->get(route('home'))->getContent();
        $this->assertSame(1, substr_count($home, 'name="robots"'));

        $page2 = $this->get(route('offers.index', ['page' => 2]))->getContent();
        $this->assertSame(1, substr_count($page2, 'name="robots"'));
        $this->assertStringContainsString('content="noindex, follow"', $page2);
    }

    public function test_titles_name_the_site_once(): void
    {
        foreach ([route('home'), route('offers.index'), route('about-us'), route('privacy-policy')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            preg_match('#<title>(.*?)</title>#s', $html, $m);
            $this->assertSame(1, substr_count($m[1] ?? '', config('app.name')), "$url: {$m[1]}");
        }
    }

    public function test_offer_pages_have_languages_a_share_image_and_breadcrumbs(): void
    {
        $offer = $this->offer();

        $this->get(route('offers.show', $offer->slug))
            ->assertOk()
            ->assertSee('hreflang="ar" href="' . route('offers.show', $offer->slug) . '?lang=ar"', false)
            ->assertSee('hreflang="x-default"', false)
            ->assertSee('<meta property="og:image" content="' . asset('images/og-home.jpg') . '"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        $this->assertFileExists(public_path('images/og-home.jpg'));
    }

    public function test_static_pages_describe_themselves(): void
    {
        $this->get(route('about-us'))
            ->assertSee('<meta name="description" content="' . e(__('seo.about_description')) . '"', false)
            ->assertSee('<link rel="canonical" href="' . route('about-us') . '"', false);
    }

    public function test_provider_pages_describe_the_business_with_an_absolute_image(): void
    {
        $provider = $this->provider(['bio' => 'Service provider profile. Please complete your details.']);

        $this->get(route('service-providers.show', $provider))
            ->assertOk()
            ->assertSee('<meta name="description" content="' . e(__('seo.provider_default_description', ['name' => 'Maple Plumbing'])) . '"', false)
            ->assertSee('"@type":"LocalBusiness"', false)
            ->assertDontSee('og:image" content="/', false);
    }
}
