<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\Offer;
use App\Models\Post;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Opens every page as each kind of visitor and fails on two things a person
 * would notice straight away: a server error, and a raw translation key
 * (e.g. "general.company") printed where words should be.
 *
 * The raw-key check exists because __() has no fallback: a missing key, or a
 * call written as __('x.y', ['default' => '...']), prints the key itself.
 */
class PageHealthTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $client;

    private ServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->client = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $this->provider = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true])->id,
            'category_id' => Category::factory()->create(['is_active' => true])->id,
            'location_id' => Location::factory()->create(['city' => 'HealthCity_' . uniqid()])->id,
            'company_name' => 'Health Check Co',
            'whatsapp_number' => '+16135550100',
            'is_active' => true,
        ]);
    }

    /** Lang file names, so "general.company" is recognised as a key. */
    private function langGroups(): array
    {
        return collect(File::files(lang_path('en')))
            ->map(fn ($f) => $f->getFilenameWithoutExtension())
            ->all();
    }

    private function parameterlessGetRoutes(): array
    {
        $uris = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (! in_array('GET', $route->methods(), true)
                || str_contains($uri, '{')
                || str_starts_with($uri, '_')
                || str_starts_with($uri, 'translations')
                || in_array($uri, ['logout'], true)) {
                continue;
            }

            $uris[$uri] = $uri;
        }

        return array_values($uris);
    }

    /**
     * @return array<int, string> problems found, one line each
     */
    private function check(array $uris, ?User $actor, string $locale = 'en'): array
    {
        $groups = implode('|', array_map('preg_quote', $this->langGroups()));
        $problems = [];

        foreach ($uris as $uri) {
            $this->app['auth']->forgetGuards();
            $request = $actor ? $this->actingAs($actor) : $this;
            $url = '/' . ltrim($uri, '/');
            $url .= (str_contains($url, '?') ? '&' : '?') . 'lang=' . $locale;
            $response = $request->get($url);
            $status = $response->getStatusCode();
            $who = $actor ? $actor->role : 'guest';

            if ($status >= 500) {
                $problems[] = "{$who} /{$uri} [{$locale}] → HTTP {$status}";

                continue;
            }

            if ($status !== 200 || ! str_contains((string) $response->headers->get('Content-Type'), 'html')) {
                continue;
            }

            // Only visible text: drop scripts, styles and tag attributes.
            $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $response->getContent());
            if (preg_match_all('/>\s*((?:' . $groups . ')\.[a-z0-9_.]+)\s*</', $html, $m)) {
                foreach (array_unique($m[1]) as $key) {
                    $problems[] = "{$who} /{$uri} [{$locale}] shows raw key \"{$key}\"";
                }
            }
        }

        return $problems;
    }

    public function test_public_pages_render_for_every_kind_of_visitor_in_every_language(): void
    {
        $public = array_filter($this->parameterlessGetRoutes(), fn ($u) => ! str_starts_with($u, 'admin'));
        $problems = [];

        foreach (['en', 'ar', 'fr'] as $locale) {
            $problems = array_merge($problems, $this->check($public, null, $locale));
        }
        $problems = array_merge(
            $problems,
            $this->check($public, $this->client),
            $this->check($public, $this->provider->user),
        );

        $this->assertSame([], $problems, "Broken public pages:\n" . implode("\n", $problems));
    }

    public function test_admin_pages_render(): void
    {
        $admin = array_filter($this->parameterlessGetRoutes(), fn ($u) => str_starts_with($u, 'admin'));

        $problems = $this->check($admin, $this->admin);

        $this->assertSame([], $problems, "Broken admin pages:\n" . implode("\n", $problems));
    }

    public function test_record_pages_render_in_every_language(): void
    {
        $offer = Offer::create([
            'service_provider_id' => $this->provider->id,
            'slug' => 'health-offer',
            'deal_type' => 'percent',
            'deal_value' => 10,
            'title_en' => 'Health Offer',
            'summary_en' => 'Summary.',
            'is_active' => true,
            'is_featured' => true,
            'ends_at' => now()->addWeek()->toDateString(),
        ]);

        $pages = [
            'service-providers/' . $this->provider->id,
            'offers/' . $offer->slug,
            'categories/' . $this->provider->category->slug,
        ];

        if ($post = Post::query()->first()) {
            $pages[] = 'blogs/' . $post->slug;
        }

        $problems = [];
        foreach (['en', 'ar', 'fr'] as $locale) {
            $problems = array_merge($problems, $this->check($pages, null, $locale));
        }
        $problems = array_merge(
            $problems,
            $this->check(['service-providers/' . $this->provider->id], $this->provider->user),
            $this->check(['admin/offers/' . $offer->slug . '/edit', 'admin/providers/' . $this->provider->id . '/edit'], $this->admin),
        );

        $this->assertSame([], $problems, "Broken record pages:\n" . implode("\n", $problems));
    }

    public function test_client_account_page_works(): void
    {
        $this->actingAs($this->client)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(__('profile.account_title'));

        $this->actingAs($this->client)->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => $this->client->email,
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame('New Name', $this->client->fresh()->name);
    }

    public function test_google_only_user_can_set_a_password_and_delete_with_their_email(): void
    {
        $user = User::factory()->create(['role' => 'client', 'is_active' => true, 'google_id' => 'g-123']);
        $user->forceFill(['password' => null])->saveQuietly();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee(__('profile.set_password'));

        $this->actingAs($user)->put(route('password.update'), [
            'password' => 'A-strong-pass-123',
            'password_confirmation' => 'A-strong-pass-123',
        ])->assertSessionHasNoErrors();
        $this->assertNotNull($user->fresh()->password);

        $other = User::factory()->create(['role' => 'client', 'is_active' => true, 'google_id' => 'g-456']);
        $other->forceFill(['password' => null])->saveQuietly();

        $this->actingAs($other)->delete(route('profile.destroy'), ['confirm_email' => 'wrong@example.com'])
            ->assertSessionHasErrorsIn('userDeletion', ['confirm_email']);
        $this->actingAs($other)->delete(route('profile.destroy'), ['confirm_email' => strtoupper($other->email)])
            ->assertRedirect('/');
        $this->assertNull(User::find($other->id));
    }

    /**
     * A byte-order mark before "<?php" is output, not code: PHP prints it at
     * the very top of every page that loads the file, ahead of <!DOCTYPE>,
     * which can push browsers into quirks mode.
     */
    public function test_no_php_file_starts_with_a_byte_order_mark(): void
    {
        $offenders = [];

        foreach (['app', 'config', 'lang', 'routes', 'resources/views', 'database', 'bootstrap'] as $dir) {
            foreach (File::allFiles(base_path($dir)) as $file) {
                if ($file->getExtension() === 'php' && str_starts_with(File::get($file->getPathname()), "\xEF\xBB\xBF")) {
                    $offenders[] = $file->getRelativePathname();
                }
            }
        }

        $this->assertSame([], $offenders, 'Files starting with a UTF-8 BOM: ' . implode(', ', $offenders));
    }

    public function test_admins_cannot_delete_themselves_from_the_account_page(): void
    {
        $this->actingAs($this->admin)->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion', ['password']);

        $this->assertNotNull($this->admin->fresh());
    }
}
