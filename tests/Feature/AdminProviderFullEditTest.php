<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Support\UploadLimits;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

/**
 * Admin provider edit is the full profile surface for ops: the same offerings
 * the provider manages themselves, plus admin-only controls (category, listing
 * visibility, certification badge).
 */
class AdminProviderFullEditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_the_edit_screen_exposes_the_full_profile_sections(): void
    {
        $provider = ServiceProvider::factory()->create([
            'company_name' => 'Acme Plumbing',
            'services_offered' => ['Repairs'],
            'languages' => ['en'],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.providers.edit', $provider))
            ->assertOk()
            ->assertSee(__('admin.provider_section_offerings'))
            ->assertSee(__('admin.provider_section_coverage'))
            ->assertSee(__('admin.provider_section_gallery'))
            ->assertSee(__('admin.provider_section_consent_proof'))
            ->assertSee('<aside class="apx-nav"', false)
            ->assertDontSee('<nav class="apx-nav"', false)
            ->assertSee('name="services_offered"', false)
            ->assertSee('name="languages[]"', false)
            ->assertSee('name="has_service_areas"', false)
            ->assertSee('name="gallery_images[]"', false)
            ->assertSee('name="photo_consent_proof"', false);
    }

    public function test_admin_can_update_services_languages_and_certification_flag(): void
    {
        $provider = ServiceProvider::factory()->create([
            'company_name' => 'Acme Plumbing',
            'services_offered' => ['Old'],
            'languages' => ['en'],
            'is_certified' => false,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), [
                'company_name' => 'Acme Plumbing',
                'services_offered' => 'Plumbing, Installation',
                'languages' => ['ar', 'fr'],
                'is_active' => '1',
                'is_certified' => '1',
                'has_service_areas' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.providers.edit', $provider));

        $fresh = $provider->fresh();

        $this->assertSame(['Plumbing', 'Installation'], $fresh->services_offered);
        $this->assertEqualsCanonicalizing(['ar', 'fr'], $fresh->languages);
        $this->assertTrue($fresh->is_certified);
    }

    public function test_admin_can_sync_service_areas_excluding_the_primary_city(): void
    {
        $primary = Location::factory()->withCity('Montreal', 45.5017, -73.5673)->create(['is_active' => true]);
        $extra = Location::factory()->withCity('Laval', 45.6066, -73.7124)->create(['is_active' => true]);

        $provider = ServiceProvider::factory()->create([
            'company_name' => 'Acme Plumbing',
            'location_id' => $primary->id,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), [
                'company_name' => 'Acme Plumbing',
                'location_id' => $primary->id,
                'is_active' => '1',
                'has_service_areas' => '1',
                'service_areas' => [$primary->id, $extra->id],
            ])
            ->assertSessionHasNoErrors();

        $ids = $provider->fresh()->locations()->pluck('locations.id')->map(fn ($id) => (int) $id)->all();

        $this->assertContains($extra->id, $ids);
        $this->assertNotContains($primary->id, $ids);
    }

    public function test_admin_can_upload_gallery_images_up_to_the_limit(): void
    {
        $provider = ServiceProvider::factory()->create(['company_name' => 'Acme Plumbing']);

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), [
                'company_name' => 'Acme Plumbing',
                'is_active' => '1',
                'gallery_images' => [
                    UploadedFile::fake()->image('one.jpg'),
                    UploadedFile::fake()->image('two.jpg'),
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $provider->fresh()->getMedia('gallery')->count());
    }

    public function test_the_upload_fields_advertise_the_limit_php_will_actually_honour(): void
    {
        $provider = ServiceProvider::factory()->create(['company_name' => 'Acme Plumbing']);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.providers.edit', $provider))
            ->assertOk()
            ->getContent();

        $gallery = UploadLimits::effectiveMb(10);
        $single = UploadLimits::effectiveMb(5);

        // The browser guard reads these, so a hard-coded 10/5 here would let an
        // oversized file through to a server that cannot take it.
        $this->assertStringContainsString('data-max-mb="'.$gallery.'"', $html);
        $this->assertStringContainsString('data-max-mb="'.$single.'"', $html);
        $this->assertLessThanOrEqual(UploadLimits::phpFileMb(), $gallery);
    }

    public function test_a_gallery_upload_php_rejected_reports_the_actual_reason(): void
    {
        $provider = ServiceProvider::factory()->create(['company_name' => 'Acme Plumbing']);

        // What PHP hands over when a file exceeds upload_max_filesize: the file
        // arrives, but unusable. Without the explaining middleware this comes
        // back as "The gallery_images.1 failed to upload", which names neither
        // the cause nor the image.
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), [
                'company_name' => 'Acme Plumbing',
                'is_active' => '1',
                'gallery_images' => [
                    UploadedFile::fake()->image('fine.jpg'),
                    new UploadedFile(__FILE__, 'huge.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true),
                ],
            ]);

        $response->assertSessionHasErrors('gallery_images.1');

        $message = session('errors')->first('gallery_images.1');
        $this->assertStringContainsString('larger than this server accepts', $message);
        $this->assertStringContainsString('gallery image #2', $message);

        // Rejected before the controller ran, so nothing was half-saved.
        $this->assertSame(0, $provider->fresh()->getMedia('gallery')->count());
    }

    public function test_admin_can_remove_gallery_images_on_save(): void
    {
        $provider = ServiceProvider::factory()->create(['company_name' => 'Acme Plumbing']);
        $media = $this->attachGalleryImage($provider, 'keep-me.jpg');

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), [
                'company_name' => 'Acme Plumbing',
                'is_active' => '1',
                'remove_gallery_ids' => [$media->id],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $provider->fresh()->getMedia('gallery')->count());
    }

    public function test_admin_can_upload_a_certification_file(): void
    {
        $provider = ServiceProvider::factory()->create([
            'company_name' => 'Acme Plumbing',
            'is_certified' => false,
            'certification' => null,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), [
                'company_name' => 'Acme Plumbing',
                'is_active' => '1',
                'certification' => UploadedFile::fake()->create('license.pdf', 120, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $fresh = $provider->fresh();

        $this->assertNotNull($fresh->certification);
        $this->assertTrue($fresh->is_certified);
        Storage::disk('public')->assertExists($fresh->certification);
    }

    public function test_admin_can_still_change_category_when_provider_cannot(): void
    {
        $locked = Category::factory()->create(['name' => 'Plumbing']);
        $target = Category::factory()->create(['name' => 'Electrical']);

        $provider = ServiceProvider::factory()->create([
            'company_name' => 'Acme Plumbing',
            'category_id' => $locked->id,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), [
                'company_name' => 'Acme Plumbing',
                'category_id' => $target->id,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($target->id, (int) $provider->fresh()->category_id);
    }

    private function attachGalleryImage(ServiceProvider $provider, string $filename): Media
    {
        try {
            return $provider->addMedia(UploadedFile::fake()->image($filename))->toMediaCollection('gallery');
        } catch (\Throwable) {
            $media = $provider->fresh()->getMedia('gallery')->sortByDesc('id')->first();
            $this->assertNotNull($media);

            return $media;
        }
    }
}
