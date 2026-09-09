<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Sweeps every page the app can render and checks that its markup closes.
 *
 * An unclosed tag is the one template mistake nothing else catches: the route
 * still answers 200, every assertion about content still passes, and the only
 * evidence is that the browser quietly re-parents everything that follows.
 * That is how the provider profile ended up rendering its whole body as
 * columns of a flex row while the test suite stayed green.
 *
 * So rather than trusting each page's own tests, this walks the route table
 * and compares opening and closing tags in what actually goes over the wire.
 */
class MarkupIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /** Pages that legitimately return no HTML document. */
    private const NON_HTML = ['sitemap.xml', 'robots.txt'];

    private User $admin;

    private ServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->provider = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true])->id,
            'category_id' => Category::factory()->create(['is_active' => true])->id,
            'location_id' => Location::factory()->create(['city' => 'MarkupCity_'.uniqid()])->id,
            'company_name' => 'Markup Test Provider',
            'bio' => 'Bio copy for the about section.',
            'phone' => '1234567890',
            'is_active' => true,
        ]);
    }

    /**
     * Opening vs closing tags in the rendered page. Script and style bodies and
     * HTML comments are stripped first, so markup written inside JavaScript
     * strings does not read as page structure.
     *
     * @return array<string, string> tag => description, empty when balanced
     */
    private function unbalancedTags(string $html): array
    {
        $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $clean = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $clean);
        $clean = preg_replace('/<!--.*?-->/s', '', $clean);

        $problems = [];

        foreach (['div', 'section', 'main', 'form', 'table', 'ul', 'nav'] as $tag) {
            $open = preg_match_all('/<'.$tag.'(?:\s[^>]*)?>/i', $clean);
            $close = preg_match_all('/<\/'.$tag.'\s*>/i', $clean);

            if ($open !== $close) {
                $problems[$tag] = sprintf('%d opened, %d closed (%+d)', $open, $close, $open - $close);
            }
        }

        // Script and style need the raw response — the strip above would have
        // eaten everything after an unclosed one, hiding the very thing being
        // looked for. Counting occurrences is not enough either: a page that
        // legitimately writes the text "<script>" inside JavaScript would read
        // as an extra opening tag. So walk the document instead, jumping from
        // each opening tag to its own closing tag, which skips whatever the
        // body says about tags.
        foreach (['style', 'script'] as $tag) {
            $offset = 0;

            while (preg_match('/<'.$tag.'(?:\s[^>]*)?>/i', $html, $m, PREG_OFFSET_CAPTURE, $offset)) {
                $bodyStart = $m[0][1] + strlen($m[0][0]);

                if (! preg_match('/<\/'.$tag.'\s*>/i', $html, $end, PREG_OFFSET_CAPTURE, $bodyStart)) {
                    $problems[$tag] = 'a <'.$tag.'> is opened near byte '.$m[0][1].' and never closed';
                    break;
                }

                $offset = $end[0][1] + strlen($end[0][0]);
            }
        }

        return $problems;
    }

    /**
     * Every GET route that takes no parameters, minus the ones that are not
     * HTML documents.
     */
    private function parameterlessGetRoutes(): array
    {
        $uris = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $uri = $route->uri();

            if (str_contains($uri, '{') || str_starts_with($uri, '_') || str_starts_with($uri, 'api/')
                || in_array($uri, self::NON_HTML, true)) {
                continue;
            }

            $uris[$uri] = $uri;
        }

        return array_values($uris);
    }

    /**
     * Checks a set of pages and reports every broken one at once — finding
     * them one failed run at a time would be needlessly slow.
     */
    private function assertPagesAreBalanced(array $uris, ?User $actor, string $label): void
    {
        $broken = [];

        foreach ($uris as $uri) {
            $request = $actor ? $this->actingAs($actor) : $this;
            $response = $request->get('/'.ltrim($uri, '/'));

            // Redirects and 403/404 render no page of their own to check.
            if (! in_array($response->getStatusCode(), [200], true)) {
                continue;
            }

            $html = $response->getContent();

            if (! str_contains($html, '<html') && ! str_contains($html, '<div')) {
                continue; // JSON, plain text, a file download
            }

            if ($problems = $this->unbalancedTags($html)) {
                foreach ($problems as $tag => $detail) {
                    $broken[] = "/{$uri} — <{$tag}>: {$detail}";
                }
            }
        }

        $this->assertSame([], $broken, sprintf(
            "Unclosed or extra tags found in the %s pages. Each line is a page whose markup does not close, "
            ."which makes the browser re-parent the rest of that page:\n%s",
            $label,
            implode("\n", $broken)
        ));
    }

    public function test_public_pages_close_every_tag_they_open(): void
    {
        $uris = array_filter(
            $this->parameterlessGetRoutes(),
            fn ($uri) => ! str_starts_with($uri, 'admin')
        );

        $this->assertNotEmpty($uris, 'No public routes were discovered — the scan is broken, not the markup.');

        $this->assertPagesAreBalanced($uris, null, 'public');
    }

    public function test_admin_pages_close_every_tag_they_open(): void
    {
        $uris = array_filter(
            $this->parameterlessGetRoutes(),
            fn ($uri) => str_starts_with($uri, 'admin')
        );

        $this->assertNotEmpty($uris, 'No admin routes were discovered — the scan is broken, not the markup.');

        $this->assertPagesAreBalanced($uris, $this->admin, 'admin');
    }

    /**
     * The detail pages, which the parameterless sweep cannot reach. These carry
     * the most markup in the app and are where the profile bug lived.
     */
    public function test_record_pages_close_every_tag_they_open(): void
    {
        $pages = [
            'service-providers/'.$this->provider->id => null,
            'admin/providers/'.$this->provider->id.'/edit' => $this->admin,
            'admin/users/'.$this->provider->user_id.'/edit' => $this->admin,
        ];

        foreach ($pages as $uri => $actor) {
            $this->assertPagesAreBalanced([$uri], $actor, $uri);
        }
    }

    /**
     * The owner's own profile page renders the entire edit form on top of the
     * public content — roughly twice the markup of any other page.
     */
    public function test_the_provider_owner_view_closes_every_tag(): void
    {
        $this->assertPagesAreBalanced(
            ['service-providers/'.$this->provider->id],
            $this->provider->user,
            'provider owner'
        );
    }
}
