<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The provider profile page is one long Blade template with deeply nested
 * markup, and a single unclosed <div> there does not fail anything — the page
 * still returns 200, the tests still pass, and the only symptom is that the
 * browser silently adopts every following section into the wrong parent. That
 * is exactly what happened: the header row holding the name and the recommend
 * button was never closed, so About Us, the gallery and the reviews all became
 * items in a horizontal flex row and the page rendered as a column of slivers
 * spilling off the side of the card.
 *
 * A status assertion cannot catch that. These check the shape of the document
 * the browser actually builds, for both the visitor and the owner — the owner
 * view carries the whole edit form and roughly twice the markup, so it is the
 * more likely of the two to break again.
 */
class ProviderProfileLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): ServiceProvider
    {
        return ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true])->id,
            'category_id' => Category::factory()->create(['is_active' => true])->id,
            'location_id' => Location::factory()->create(['city' => 'LayoutCity_'.uniqid()])->id,
            'company_name' => 'Layout Test Provider',
            'bio' => 'A description long enough to occupy the About Us section.',
            'phone' => '1234567890',
            'is_active' => true,
        ]);
    }

    /**
     * Parses the page the way a browser would and returns the element children
     * of .profile-content — the sections that must sit one under another.
     */
    private function profileSections(string $html): array
    {
        $doc = new \DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($doc);
        $content = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' profile-content ')]")->item(0);

        $this->assertNotNull($content, 'The .profile-content wrapper is missing from the page.');

        $sections = [];
        foreach ($content->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $sections[] = $node->getAttribute('class') ?: '('.$node->tagName.')';
            }
        }

        return $sections;
    }

    public function test_the_visitor_view_stacks_its_sections_instead_of_nesting_them(): void
    {
        $provider = $this->provider();

        $html = $this->get(route('service-providers.show', $provider))->assertOk()->getContent();

        $sections = $this->profileSections($html);

        // One child means everything was swallowed by the first unclosed
        // element — the failure this test exists for.
        $this->assertGreaterThan(
            1,
            count($sections),
            'Every section collapsed into one element: a tag in the profile page is not closed.'
        );

        // The header row holds the name and the recommend button and nothing
        // else. If About Us or the reviews are inside it, they are being laid
        // out as columns of a flex row rather than stacked sections.
        $doc = new \DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($doc);

        $insideHeader = $xpath->query(
            "//div[contains(@class, 'justify-content-between')][contains(@class, 'align-items-start')]//div[@id='reviews']"
        );

        $this->assertSame(0, $insideHeader->length, 'The reviews section is nested inside the name/button header row.');
    }

    public function test_the_owner_edit_view_stacks_its_sections_too(): void
    {
        $provider = $this->provider();

        $html = $this->actingAs($provider->user)
            ->get(route('service-providers.show', $provider))
            ->assertOk()
            ->getContent();

        $this->assertGreaterThan(
            1,
            count($this->profileSections($html)),
            'Every section collapsed into one element in the owner view: a tag is not closed.'
        );
    }

    /**
     * The other way this page broke: it ended with a `<style>` that was opened
     * and never closed. A browser then swallows everything after it — the
     * closing </main>, the toast container, part of the footer — as stylesheet
     * text, and none of it becomes elements. Nothing errors; the page just
     * loses its bottom half.
     *
     * Checked on the raw response rather than a parsed document on purpose:
     * PHP's parser quietly tolerates the unclosed tag, so a DOM-based check
     * passes while real browsers break. Counting the tags is what actually
     * reproduces it.
     */
    public function test_style_and_script_tags_are_balanced_in_the_response(): void
    {
        $provider = $this->provider();

        foreach (['visitor' => null, 'owner' => $provider->user] as $who => $actor) {
            $request = $actor ? $this->actingAs($actor) : $this;
            $html = $request->get(route('service-providers.show', $provider))->assertOk()->getContent();

            foreach (['style', 'script'] as $tag) {
                $opened = preg_match_all('/<'.$tag.'(?:\s[^>]*)?>/i', $html);
                $closed = preg_match_all('/<\/'.$tag.'\s*>/i', $html);

                $this->assertSame(
                    $opened,
                    $closed,
                    "The {$who} view has {$opened} <{$tag}> tags but {$closed} closing ones. "
                    ."An unclosed <{$tag}> makes the browser swallow the rest of the page as its contents."
                );
            }
        }
    }

    /**
     * The page carries two WhatsApp calls to action: one near the top for
     * phones, and one in the contact card. On a wide screen the card sits in
     * its own column and the early one is hidden; on a phone the card stacks
     * underneath the profile, so without a matching rule on the card's button
     * the visitor scrolls past the same green button twice.
     *
     * Both are always in the HTML — which one shows is decided by Bootstrap's
     * display utilities — so this resolves those utilities the way the browser
     * would and counts what is actually visible at each width.
     */
    public function test_exactly_one_whatsapp_call_to_action_shows_at_every_width(): void
    {
        $provider = $this->provider();
        $provider->update(['whatsapp_number' => '+15551234567']);

        $html = $this->get(route('service-providers.show', $provider))->assertOk()->getContent();

        $doc = new \DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($doc);
        $label = __('service_provider.contact_whatsapp');
        $nodes = $xpath->query("//button[contains(., '{$label}')] | //a[contains(., '{$label}')]");

        $this->assertGreaterThan(0, $nodes->length, 'No WhatsApp call to action was rendered at all.');

        foreach (['phone' => false, 'desktop' => true] as $width => $isLarge) {
            $visible = 0;

            foreach ($nodes as $node) {
                if ($this->isVisibleAt($node, $isLarge)) {
                    $visible++;
                }
            }

            $this->assertSame(1, $visible, "Expected one WhatsApp button on {$width}, found {$visible}.");
        }
    }

    /**
     * Resolves Bootstrap's d-* utilities on an element and its ancestors.
     * `d-none` hides everywhere; `d-lg-none` hides from lg up; `d-lg-<value>`
     * shows again from lg up. The last rule that applies at a width wins,
     * which is why the lg variants are checked after the base one.
     */
    private function isVisibleAt(\DOMNode $node, bool $isLarge): bool
    {
        for ($n = $node; $n instanceof \DOMElement; $n = $n->parentNode) {
            $classes = preg_split('/\s+/', $n->getAttribute('class') ?: '', -1, PREG_SPLIT_NO_EMPTY);

            $display = in_array('d-none', $classes, true) ? 'none' : null;

            if ($isLarge) {
                foreach ($classes as $class) {
                    if ($class === 'd-lg-none') {
                        $display = 'none';
                    } elseif (str_starts_with($class, 'd-lg-')) {
                        $display = substr($class, 5);
                    }
                }
            }

            if ($display === 'none') {
                return false;
            }
        }

        return true;
    }

    /**
     * The sidebar is a sibling of the main column, never a descendant of it.
     * Swallowing it is the other way this page loses its two-column layout.
     */
    public function test_the_contact_sidebar_is_not_swallowed_by_the_main_column(): void
    {
        $provider = $this->provider();

        $html = $this->get(route('service-providers.show', $provider))->assertOk()->getContent();

        $doc = new \DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($doc);

        $nested = $xpath->query(
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' col-lg-8 ')]"
            ."//div[contains(concat(' ', normalize-space(@class), ' '), ' col-lg-4 ')]"
        );

        $this->assertSame(0, $nested->length, 'The sidebar column ended up inside the main column.');
    }
}
