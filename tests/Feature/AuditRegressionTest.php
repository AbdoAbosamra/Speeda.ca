<?php

namespace Tests\Feature;

use App\Actions\TrackProviderClickAction;
use App\Domain\SEO\Services\SitemapService;
use App\Http\Controllers\GalleryController;
use App\Http\Middleware\SecurityHeaders;
use App\Models\Category;
use App\Models\ServiceProvider;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SafeCategoryUpdateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mockery;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Tests\TestCase;

class AuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_upload_generates_webp_images_and_replacement_keeps_other_images(): void
    {
        Storage::fake('public');
        $provider = ServiceProvider::factory()->create();
        $this->actingAs($provider->user);
        $response = $this->postJson(route('provider.gallery.store', $provider), [
            'gallery_image' => UploadedFile::fake()->image('first.jpg', 800, 400),
        ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('count', 1);
        $old = $provider->fresh()->getFirstMedia('gallery');
        $this->assertTrue($old->hasGeneratedConversion('gallery_thumb'));
        $this->assertTrue($old->hasGeneratedConversion('gallery_large'));
        Storage::disk('public')->assertExists($old->getPathRelativeToRoot('gallery_thumb'));
        $this->assertStringEndsWith('.webp', $response->json('media.thumb_url'));
        $this->postJson(route('provider.gallery.update', [$provider, $old->id]), [
            'gallery_image' => UploadedFile::fake()->image('second.png', 500, 500),
        ])->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('media', ['id' => $old->id]);
        $this->assertCount(1, $provider->fresh()->getMedia('gallery'));
    }

    public function test_failed_replacement_never_returns_or_deletes_the_existing_image(): void
    {
        Storage::fake('public');
        $provider = ServiceProvider::factory()->create();
        $old = $provider->addMedia(UploadedFile::fake()->image('old.jpg'))->toMediaCollection('gallery');
        $this->actingAs($provider->user);
        $mock = Mockery::mock(ServiceProvider::class)->makePartial();
        $mock->setRawAttributes($provider->getAttributes(), true);
        $adder = Mockery::mock(FileAdder::class);
        $adder->shouldReceive('withCustomProperties')->once()->andReturnSelf();
        $adder->shouldReceive('toMediaCollection')->once()->andThrow(new \RuntimeException('Storage failed'));
        $mock->shouldReceive('addMedia')->once()->andReturn($adder);
        $request = Request::create('/', 'POST', [], [], ['gallery_image' => UploadedFile::fake()->image('new.jpg')]);
        $response = app(GalleryController::class)->update($request, $mock, $old->id);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertDatabaseHas('media', ['id' => $old->id]);
        Storage::disk('public')->assertExists($old->getPathRelativeToRoot());
    }

    public function test_gallery_falls_back_to_original_then_placeholder(): void
    {
        Storage::fake('public');
        $provider = ServiceProvider::factory()->create();
        $media = $provider->addMedia(UploadedFile::fake()->image('original.jpg'))->toMediaCollection('gallery');
        Storage::disk('public')->delete($media->getPathRelativeToRoot('gallery_thumb'));
        $this->assertSame($provider->getMediaPublicUrl($media), $provider->getMediaPublicUrl($media, 'gallery_thumb'));
        Storage::disk('public')->delete($media->getPathRelativeToRoot());
        $this->assertSame($provider->default_image_url, $provider->getMediaPublicUrl($media, 'gallery_thumb'));
    }

    public function test_gallery_rejects_access_to_another_providers_image(): void
    {
        Storage::fake('public');
        $owner = ServiceProvider::factory()->create();
        $other = ServiceProvider::factory()->create();
        $media = $other->addMedia(UploadedFile::fake()->image('private.jpg'))->toMediaCollection('gallery');
        $this->actingAs($owner->user)->deleteJson(route('provider.gallery.destroy', [$owner, $media->id]))->assertNotFound();
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_analytics_supports_required_legacy_ip_column_alongside_session_hash(): void
    {
        $provider = ServiceProvider::factory()->create();
        Schema::table('analytics', function ($table) {
            $table->string('ip_address', 45)->nullable(false)->change();
        });
        Cache::forget('analytics_table_columns');
        $this->assertTrue(app(TrackProviderClickAction::class)->execute($provider->id, 'click_whatsapp'));
        $row = DB::table('analytics')->where('provider_id', $provider->id)->first();
        $this->assertStringStartsWith('hash:', $row->ip_address);
        $this->assertSame(64, strlen($row->session_hash));
    }

    public function test_static_provider_routes_are_not_consumed_by_the_public_profile_route(): void
    {
        foreach (['dashboard', 'profile'] as $page) {
            $matched = Route::getRoutes()->match(Request::create('/service-providers/'.$page));
            $this->assertNotSame('service-providers.show', $matched->getName());
        }
        $this->assertSame('service-providers.show', Route::getRoutes()->match(Request::create('/service-providers/123'))->getName());
    }

    public function test_endorsement_toggle_repairs_stale_count_without_underflow(): void
    {
        $provider = ServiceProvider::factory()->create(['endorsement_count' => 0]);
        $client = User::factory()->client()->create();
        $provider->endorsements()->create(['user_id' => $client->id]);
        $this->actingAs($client)->post(route('endorsements.toggle', $provider))->assertSessionHas('success');
        $this->assertSame(0, $provider->fresh()->endorsement_count);
        $this->assertSame(0, $provider->endorsements()->count());
    }

    public function test_security_headers_apply_and_hsts_is_https_production_only(): void
    {
        $this->app->instance('env', 'production');
        $middleware = new SecurityHeaders;
        $secure = $middleware->handle(Request::create('https://speeda.ca/'), fn () => response('OK'));
        $this->assertSame('SAMEORIGIN', $secure->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $secure->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString("frame-ancestors 'self'", $secure->headers->get('Content-Security-Policy'));
        $this->assertSame('max-age=31536000', $secure->headers->get('Strict-Transport-Security'));
        $plain = $middleware->handle(Request::create('http://localhost/'), fn () => response('OK'));
        $this->assertFalse($plain->headers->has('Strict-Transport-Security'));
    }

    public function test_production_verification_rejects_local_configuration(): void
    {
        $this->artisan('deploy:verify')->assertFailed();
        $this->app->instance('env', 'production');
        config(['app.debug' => false, 'app.url' => 'https://speeda.ca', 'session.encrypt' => true, 'session.secure' => true, 'view.compiled' => base_path('bootstrap/cache/views')]);
        $this->artisan('deploy:verify')->assertSuccessful();

        // A session cookie that may travel over plain HTTP blocks the release.
        config(['session.secure' => false]);
        $this->artisan('deploy:verify')->assertFailed();
    }

    public function test_category_initialization_is_portable_and_preserves_existing_catalog(): void
    {
        $this->seed(CategorySeeder::class);
        $this->seed(SafeCategoryUpdateSeeder::class);
        $category = Category::firstOrFail();
        $category->update(['name' => 'Edited catalog entry']);
        $count = Category::count();
        $this->seed(CategorySeeder::class);
        $this->assertSame($count, Category::count());
        $this->assertSame('Edited catalog entry', $category->fresh()->name);
    }

    public function test_admin_alias_uses_the_same_login_throttle_as_email(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test', 'role' => 'admin']);
        config(['auth.admin_email' => $admin->email]);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['login' => 'admin', 'password' => 'incorrect-password', 'role' => 'client'])
                ->assertSessionHasErrors('login');
        }
        $this->assertTrue(RateLimiter::tooManyAttempts('admin@example.test|127.0.0.1', 5));
        $this->post('/login', ['login' => 'admin', 'password' => 'password', 'role' => 'client'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_sitemap_uses_requested_domain_without_changing_url_generator_state(): void
    {
        Storage::fake('sitemap-test');
        $originalPublicPath = public_path();
        $generator = URL::getFacadeRoot();
        $originalUrl = url('/probe');
        $this->app->usePublicPath(Storage::disk('sitemap-test')->path(''));
        try {
            app(SitemapService::class)->generate('https://speeda.ca');
            $xml = file_get_contents(public_path('sitemap.xml'));
            $this->assertStringContainsString('https://speeda.ca', $xml);
            $this->assertStringNotContainsString('localhost', $xml);
            $this->assertSame($generator, URL::getFacadeRoot());
            $this->assertSame($originalUrl, url('/probe'));
        } finally {
            $this->app->usePublicPath($originalPublicPath);
        }
    }
}
