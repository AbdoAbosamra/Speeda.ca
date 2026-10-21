<?php

namespace Tests\Feature\ServiceProvider;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Providers cannot upload gallery images through the profile form or the old
 * upload endpoints. Previously uploaded images stay visible and untouched.
 */
class ProfileGalleryLimitTest extends TestCase
{
    use RefreshDatabase;

    private ServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $location = Location::firstOrCreate(['city' => 'Toronto'], ['is_active' => true, 'country' => 'Canada']);
        $location->update(['is_active' => true]);

        $this->provider = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true])->id,
            'category_id' => Category::factory(),
            'location_id' => $location->id,
            'phone' => '+14165550101',
            'whatsapp_number' => '+14385550101',
            'is_active' => true,
        ]);
    }

    private function update(array $files = [])
    {
        return $this->actingAs($this->provider->user)->put(
            route('service-providers.profile.update', $this->provider),
            [
                'business_name' => 'Updated Business Co',
                'phone' => $this->provider->phone,
                'whatsapp_country_code' => '+1',
                'whatsapp_number' => '4385550101',
                'location_id' => $this->provider->location_id,
                'gallery_images' => $files,
            ]
        );
    }

    private function images(int $count): array
    {
        return array_map(fn ($i) => UploadedFile::fake()->image("photo{$i}.jpg"), range(1, $count));
    }

    public function test_even_one_gallery_upload_is_rejected_for_a_provider_with_no_images(): void
    {
        $this->update($this->images(1))
            ->assertSessionHasErrors('gallery_images');

        $this->assertSame(0, $this->provider->fresh()->getMedia('gallery')->count());
    }

    public function test_uploads_are_rejected_without_changing_existing_images(): void
    {
        foreach ($this->images(ServiceProvider::MAX_GALLERY_IMAGES - 1) as $file) {
            $this->provider->addMedia($file)->toMediaCollection('gallery');
        }

        $existing = $this->provider->fresh()->getMedia('gallery');
        $this->update($this->images(1))->assertSessionHasErrors('gallery_images');

        $this->assertSame(
            $existing->modelKeys(),
            $this->provider->fresh()->getMedia('gallery')->modelKeys()
        );
        foreach ($existing as $media) {
            Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
        }
    }

    public function test_old_upload_and_replacement_endpoints_cannot_change_existing_images(): void
    {
        $media = $this->provider->addMedia($this->images(1)[0])->toMediaCollection('gallery');
        $this->actingAs($this->provider->user);
        $base = '/service-providers/profile/'.$this->provider->id.'/gallery';

        $this->postJson($base, ['gallery_image' => $this->images(1)[0]])->assertNotFound();
        $this->postJson($base.'/'.$media->id.'/replace', ['gallery_image' => $this->images(1)[0]])->assertNotFound();
        $this->patchJson($base.'/'.$media->id, ['gallery_image' => $this->images(1)[0]])->assertStatus(405);

        $this->assertSame([$media->id], $this->provider->fresh()->getMedia('gallery')->modelKeys());
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_profile_edits_without_uploads_preserve_existing_images(): void
    {
        $media = $this->provider->addMedia($this->images(1)[0])->toMediaCollection('gallery');

        $this->update()->assertSessionHasNoErrors()
            ->assertRedirect(route('service-providers.show', $this->provider));

        $this->assertSame('Updated Business Co', $this->provider->fresh()->company_name);
        $this->assertSame([$media->id], $this->provider->fresh()->getMedia('gallery')->modelKeys());
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_empty_galleries_have_no_gallery_section_or_upload_controls(): void
    {
        $this->actingAs($this->provider->user)
            ->get(route('service-providers.show', $this->provider))->assertOk()
            ->assertDontSee('id="edit-gallery"', false)
            ->assertDontSee('public-gallery-grid', false)
            ->assertDontSee('galleryAddInput', false)
            ->assertDontSee('galleryReplaceInput', false)
            ->assertDontSee('name="gallery_images', false);
    }

    public function test_existing_images_still_appear_to_visitors_and_the_owner_without_upload_controls(): void
    {
        $media = $this->provider->addMedia($this->images(1)[0])->toMediaCollection('gallery');
        $thumbUrl = $this->provider->getMediaPublicUrl($media, 'gallery_thumb');

        foreach ([null, $this->provider->user] as $viewer) {
            if ($viewer) {
                $this->actingAs($viewer);
            }

            $this->get(route('service-providers.show', $this->provider))->assertOk()
                ->assertSee('public-gallery-grid', false)
                ->assertSee($thumbUrl, false)
                ->assertDontSee('galleryAddInput', false)
                ->assertDontSee('galleryReplaceInput', false)
                ->assertDontSee('name="gallery_images', false)
                ->assertDontSee('startReplace', false);
        }

        $this->assertSame([$media->id], $this->provider->fresh()->getMedia('gallery')->modelKeys());
    }

    public function test_owners_can_still_delete_their_existing_images(): void
    {
        $media = $this->provider->addMedia($this->images(1)[0])->toMediaCollection('gallery');

        $this->actingAs($this->provider->user)
            ->deleteJson(route('provider.gallery.destroy', [$this->provider, $media->id]))
            ->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }
}
