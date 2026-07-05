<?php

namespace Tests\Feature;

use App\Models\LegalPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLegalPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->client()->create([
            'email' => 'legal-admin@example.test',
        ]);

        config(['auth.admins' => [$this->admin->email]]);
    }

    public function test_admin_can_view_legal_pages_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.legal-pages.index'))
            ->assertOk()
            ->assertSee('Policies & Privacy')
            ->assertSee('Privacy Policy')
            ->assertSee('Terms of Service')
            ->assertSee('Static fallback');
    }

    public function test_admin_can_publish_privacy_policy_override(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.legal-pages.store'), $this->payload([
                'slug' => 'privacy-policy',
                'page_type' => LegalPage::TYPE_PRIVACY_POLICY,
                'status' => LegalPage::STATUS_PUBLISHED,
                'title_en' => 'CMS Privacy Policy',
                'content_en' => '<h2>CMS Privacy Content</h2><script>alert(1)</script><p onclick="evil()">Visible CMS privacy copy.</p>',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('legal_pages', [
            'slug' => 'privacy-policy',
            'status' => LegalPage::STATUS_PUBLISHED,
            'title_en' => 'CMS Privacy Policy',
        ]);

        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertSee('CMS Privacy Policy')
            ->assertSee('CMS Privacy Content')
            ->assertSee('Visible CMS privacy copy.')
            ->assertDontSee('alert(1)')
            ->assertDontSee('evil()');
    }

    public function test_admin_can_publish_custom_legal_page(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.legal-pages.store'), $this->payload([
                'slug' => 'marketplace-rules',
                'page_type' => LegalPage::TYPE_POLICY,
                'status' => LegalPage::STATUS_PUBLISHED,
                'title_en' => 'Marketplace Rules',
                'content_en' => '<h2>Provider Rules</h2><p>Custom public legal page.</p>',
            ]))
            ->assertRedirect();

        $this->get(route('legal-pages.show', 'marketplace-rules'))
            ->assertOk()
            ->assertSee('Marketplace Rules')
            ->assertSee('Provider Rules')
            ->assertSee('Custom public legal page.');
    }

    public function test_draft_custom_legal_page_is_hidden_from_public_site(): void
    {
        LegalPage::factory()->draft()->create([
            'slug' => 'draft-policy',
            'title_en' => 'Draft Policy',
        ]);

        $this->get(route('legal-pages.show', 'draft-policy'))
            ->assertNotFound();
    }

    public function test_deleting_privacy_override_restores_static_fallback(): void
    {
        $page = LegalPage::factory()->privacyPolicy()->published()->create([
            'content_en' => '<h2>Temporary CMS Privacy Copy</h2>',
        ]);

        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertSee('Temporary CMS Privacy Copy');

        $this->actingAs($this->admin)
            ->delete(route('admin.legal-pages.destroy', $page))
            ->assertRedirect(route('admin.legal-pages.index'));

        $this->assertSoftDeleted('legal_pages', [
            'id' => $page->id,
        ]);

        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertDontSee('Temporary CMS Privacy Copy');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'slug' => 'test-legal-page',
            'page_type' => LegalPage::TYPE_CUSTOM,
            'status' => LegalPage::STATUS_DRAFT,
            'allow_indexing' => '1',
            'title_en' => 'Test Legal Page',
            'title_ar' => 'صفحة قانونية تجريبية',
            'title_fr' => 'Page juridique de test',
            'content_en' => '<h2>English Section</h2><p>English legal copy.</p>',
            'content_ar' => '<h2>قسم عربي</h2><p>نص قانوني عربي.</p>',
            'content_fr' => '<h2>Section française</h2><p>Texte juridique français.</p>',
            'summary_en' => 'English legal summary.',
            'summary_ar' => 'ملخص قانوني عربي.',
            'summary_fr' => 'Résumé juridique français.',
        ], $overrides);
    }
}
