<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The admin can upload a profile photo on a provider's behalf, optionally
 * attaching a proof image (e.g. a screenshot of the provider agreeing over
 * WhatsApp). The photo update takes effect immediately either way — there is
 * no provider-side confirmation step. The provider simply sees, on their own
 * profile, that their team uploaded the photo and (if attached) the proof.
 *
 * None of this — the source note, the proof link — is ever visible to a site
 * visitor or another provider. That visibility boundary is what most of these
 * tests are actually pinning down.
 */
class AdminPhotoConsentTest extends TestCase
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

    /* ================================================================== */
    /*  Admin upload                                                       */
    /* ================================================================== */

    public function test_admin_can_upload_a_photo_with_no_proof_attached(): void
    {
        $provider = ServiceProvider::factory()->create(['company_name' => 'Acme Plumbing']);

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), $this->baseFields($provider) + [
                'profile_image' => UploadedFile::fake()->image('photo.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $fresh = $provider->fresh();

        $this->assertNotNull($fresh->profile_image);
        Storage::disk('public')->assertExists($fresh->profile_image);

        $this->assertNotNull($fresh->photo_uploaded_by_admin_at);
        $this->assertSame($this->admin->id, $fresh->photo_consent_admin_id);
        $this->assertNull($fresh->photo_consent_proof_path);
        $this->assertFalse($fresh->hasPhotoConsentProof());
    }

    public function test_admin_can_upload_a_photo_with_a_proof_image_attached(): void
    {
        $provider = ServiceProvider::factory()->create(['company_name' => 'Acme Plumbing']);

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), $this->baseFields($provider) + [
                'profile_image' => UploadedFile::fake()->image('photo.jpg'),
                'photo_consent_proof' => UploadedFile::fake()->image('whatsapp-screenshot.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $fresh = $provider->fresh();

        $this->assertTrue($fresh->hasPhotoConsentProof());
        Storage::disk('public')->assertExists($fresh->photo_consent_proof_path);
    }

    public function test_uploading_a_new_admin_photo_deletes_the_old_photo_file(): void
    {
        $provider = ServiceProvider::factory()->create(['company_name' => 'Acme Plumbing']);
        $provider->update(['profile_image' => 'service-providers/profiles/old.jpg']);
        Storage::disk('public')->put('service-providers/profiles/old.jpg', 'fake');

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), $this->baseFields($provider) + [
                'profile_image' => UploadedFile::fake()->image('new-photo.jpg'),
            ])
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('service-providers/profiles/old.jpg');
    }

    public function test_editing_provider_fields_without_a_photo_is_unaffected(): void
    {
        $provider = ServiceProvider::factory()->create(['company_name' => 'Acme Plumbing']);

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), $this->baseFields($provider, companyName: 'Acme Plumbing Co'))
            ->assertSessionHasNoErrors();

        $fresh = $provider->fresh();
        $this->assertSame('Acme Plumbing Co', $fresh->company_name);
        $this->assertNull($fresh->photo_uploaded_by_admin_at);
    }

    /* ================================================================== */
    /*  Provider uploading their own photo clears the trail                */
    /* ================================================================== */

    public function test_the_provider_uploading_their_own_photo_clears_the_admin_trail_and_deletes_the_proof_file(): void
    {
        $provider = ServiceProvider::factory()->create();
        $provider->recordAdminPhotoUpload($this->admin->id, 'service-providers/photo-consent-proofs/proof.jpg');
        Storage::disk('public')->put('service-providers/photo-consent-proofs/proof.jpg', 'fake');

        $this->actingAs($provider->user)
            ->postJson(route('service-providers.profile.image-upload'), [
                'profile_image' => UploadedFile::fake()->image('own-photo.jpg'),
            ])
            ->assertOk();

        $fresh = $provider->fresh();
        $this->assertNull($fresh->photo_uploaded_by_admin_at);
        $this->assertNull($fresh->photo_consent_admin_id);
        $this->assertNull($fresh->photo_consent_proof_path);
        $this->assertFalse($fresh->photoWasUploadedByAdmin());

        Storage::disk('public')->assertMissing('service-providers/photo-consent-proofs/proof.jpg');
    }

    /* ================================================================== */
    /*  Visibility: admin + owner only, never the public                   */
    /* ================================================================== */

    public function test_the_note_is_shown_to_the_owner_when_the_admin_uploaded_the_photo(): void
    {
        $provider = ServiceProvider::factory()->create();
        $provider->recordAdminPhotoUpload($this->admin->id);

        $this->actingAs($provider->user)
            ->get(route('service-providers.show', $provider))
            ->assertOk()
            ->assertSee(__('service_provider.photo_consent_info_title'));
    }

    public function test_the_note_is_never_shown_to_a_guest(): void
    {
        $provider = ServiceProvider::factory()->create();
        $provider->recordAdminPhotoUpload($this->admin->id);

        $this->get(route('service-providers.show', $provider))
            ->assertOk()
            ->assertDontSee(__('service_provider.photo_consent_info_title'));
    }

    public function test_the_note_is_never_shown_to_a_different_logged_in_provider(): void
    {
        $provider = ServiceProvider::factory()->create();
        $provider->recordAdminPhotoUpload($this->admin->id);

        $otherProvider = ServiceProvider::factory()->create();

        $this->actingAs($otherProvider->user)
            ->get(route('service-providers.show', $provider))
            ->assertOk()
            ->assertDontSee(__('service_provider.photo_consent_info_title'));
    }

    public function test_the_note_is_not_shown_for_a_photo_the_provider_uploaded_themselves(): void
    {
        $provider = ServiceProvider::factory()->create(['profile_image' => 'service-providers/profiles/own.jpg']);

        $this->actingAs($provider->user)
            ->get(route('service-providers.show', $provider))
            ->assertOk()
            ->assertDontSee(__('service_provider.photo_consent_info_title'));
    }

    public function test_the_admin_edit_screen_shows_the_photo_source(): void
    {
        $provider = ServiceProvider::factory()->create();
        $provider->recordAdminPhotoUpload($this->admin->id, 'service-providers/photo-consent-proofs/proof.jpg');

        $this->actingAs($this->admin)
            ->get(route('admin.providers.edit', $provider))
            ->assertOk()
            ->assertSee(__('admin.photo_consent_proof_view'));
    }

    public function test_no_photo_consent_route_exists_anymore(): void
    {
        // The confirmation step was removed entirely, not just hidden.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('service-providers.profile.photo-consent.confirm'));
    }

    /**
     * The minimum valid field set update() requires, so each test only has to
     * spell out what it is actually asserting about.
     */
    private function baseFields(ServiceProvider $provider, ?string $companyName = null): array
    {
        return [
            'company_name' => $companyName ?? $provider->company_name,
        ];
    }
}
