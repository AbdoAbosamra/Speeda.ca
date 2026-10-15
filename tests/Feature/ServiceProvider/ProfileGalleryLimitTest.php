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
 * The AJAX gallery widget caps a gallery at ServiceProvider::MAX_GALLERY_IMAGES,
 * but the profile form also accepts gallery_images[]. That path must hold the
 * same cap, or a hand-made request grows the gallery without limit.
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
            'user_id' => User::factory()->create(['is_active' => true])->id,
            'category_id' => Category::factory(),
            'location_id' => $location->id,
            'phone' => '+14165550101',
            'whatsapp_number' => '+14385550101',
        ]);
    }

    private function update(array $files)
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

    public function test_more_files_than_the_gallery_holds_are_rejected(): void
    {
        $this->update($this->images(ServiceProvider::MAX_GALLERY_IMAGES + 1))
            ->assertSessionHasErrors('gallery_images');

        $this->assertSame(0, $this->provider->fresh()->getMedia('gallery')->count());
    }

    public function test_new_files_only_fill_the_slots_that_are_left(): void
    {
        foreach ($this->images(ServiceProvider::MAX_GALLERY_IMAGES - 1) as $file) {
            $this->provider->addMedia($file)->toMediaCollection('gallery');
        }

        $this->update($this->images(2))->assertSessionHasNoErrors();

        $this->assertSame(
            ServiceProvider::MAX_GALLERY_IMAGES,
            $this->provider->fresh()->getMedia('gallery')->count()
        );
    }
}
