<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\OfferComment;
use App\Models\OfferLike;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Support\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Favorites (client accounts only), comment moderation, and the "publish
 * your offer" WhatsApp contact. Public likes and comments are paused.
 */
class OfferEngagementTest extends TestCase
{
    use RefreshDatabase;

    private Offer $offer;

    private User $admin;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCookie(config('session.cookie'), str_repeat('e', 40))->withCredentials();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->client = User::factory()->create(['role' => 'client', 'is_active' => true]);

        $provider = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true])->id,
            'company_name' => 'Engage Co',
            'whatsapp_number' => '6135550199',
            'is_active' => true,
        ]);

        $this->offer = Offer::create([
            'service_provider_id' => $provider->id,
            'slug' => 'engage-offer',
            'deal_type' => 'percent',
            'deal_value' => 20,
            'title_en' => 'Engage Offer',
            'summary_en' => 'Summary.',
            'is_active' => true,
            'is_featured' => true,
            'starts_at' => now()->subDay()->toDateString(),
            'ends_at' => now()->addWeek()->toDateString(),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Likes                                                              */
    /* ------------------------------------------------------------------ */

    /* Temporarily disabled with the public offer like routes and UI.
    public function test_a_guest_can_like_and_unlike(): void
    {
        $this->postJson(route('offers.like', $this->offer->slug))
            ->assertOk()->assertJson(['liked' => true, 'count' => 1]);

        $this->assertSame(1, $this->offer->fresh()->likes_count);

        $this->postJson(route('offers.like', $this->offer->slug))
            ->assertOk()->assertJson(['liked' => false, 'count' => 0]);

        $this->assertSame(0, OfferLike::count());
    }

    public function test_each_account_and_each_guest_counts_once(): void
    {
        $this->postJson(route('offers.like', $this->offer->slug))->assertJson(['count' => 1]);
        $this->actingAs($this->client)->postJson(route('offers.like', $this->offer->slug))->assertJson(['liked' => true, 'count' => 2]);

        $this->assertSame(2, $this->offer->fresh()->likes_count);
        $this->actingAs($this->client)->get(route('offers.show', $this->offer->slug))->assertSee(__('offers.liked'));
    }

    */

    public function test_offers_cannot_be_liked_or_commented_while_paused(): void
    {
        $this->postJson('/offers/'.$this->offer->slug.'/like')->assertNotFound();
        $this->postJson('/offers/'.$this->offer->slug.'/comments', ['author_name' => 'Sam', 'body' => 'Hello there'])->assertNotFound();

        $this->offer->update(['ends_at' => now()->subDays(2)->toDateString()]);

        $this->postJson('/offers/'.$this->offer->slug.'/like')->assertNotFound();
        $this->postJson('/offers/'.$this->offer->slug.'/comments', ['author_name' => 'Sam', 'body' => 'Hello there'])->assertNotFound();
        $this->assertSame(0, OfferLike::count());
        $this->assertSame(0, OfferComment::count());
    }

    public function test_paused_offer_engagement_is_hidden_and_existing_data_is_preserved(): void
    {
        $like = OfferLike::create(['offer_id' => $this->offer->id, 'user_id' => $this->client->id]);
        $comment = OfferComment::create([
            'offer_id' => $this->offer->id,
            'author_name' => 'Existing visitor',
            'body' => 'Previously approved offer comment.',
            'status' => OfferComment::STATUS_APPROVED,
            'locale' => 'en',
        ]);
        $this->offer->forceFill(['likes_count' => 1, 'comments_count' => 1])->save();

        foreach ([null, $this->client, $this->offer->serviceProvider->user] as $viewer) {
            if ($viewer) {
                $this->actingAs($viewer);
            }

            $this->get(route('offers.show', $this->offer->slug))->assertOk()
                ->assertDontSee('data-so-like', false)
                ->assertDontSee('data-like-count', false)
                ->assertDontSee('data-so-comment', false)
                ->assertDontSee('id="comments"', false)
                ->assertDontSee($comment->body);
            $this->get(route('offers.index'))->assertOk()
                ->assertDontSee('class="so-meta-likes"', false);
        }

        $this->assertModelExists($like);
        $this->assertModelExists($comment);
        $this->assertSame(1, $this->offer->fresh()->likes_count);
        $this->assertSame(1, $this->offer->fresh()->comments_count);
    }

    /* ------------------------------------------------------------------ */
    /*  Comments                                                           */
    /* ------------------------------------------------------------------ */

    /* Temporarily disabled with the public offer comment routes and UI.
    public function test_a_guest_comment_waits_for_approval_before_it_appears(): void
    {
        $this->postJson(route('offers.comment', $this->offer->slug), [
            'author_name' => 'Sara',
            'body' => 'Great deal, booked my first lesson!',
        ])->assertOk()->assertJson(['ok' => true]);

        $comment = OfferComment::firstOrFail();
        $this->assertSame('pending', $comment->status);
        $this->get(route('offers.show', $this->offer->slug))->assertDontSee('Great deal, booked my first lesson!');

        $this->actingAs($this->admin)->get(route('admin.offers.comments'))->assertOk()->assertSee('Great deal, booked my first lesson!');
        $this->actingAs($this->admin)->patch(route('admin.offers.comments.approve', $comment))->assertRedirect();

        $this->assertSame(1, $this->offer->fresh()->comments_count);
        $this->app['auth']->forgetGuards();
        $this->get(route('offers.show', $this->offer->slug))->assertSee('Great deal, booked my first lesson!')->assertSee('Sara');

        $this->actingAs($this->admin)->patch(route('admin.offers.comments.reject', $comment))->assertRedirect();
        $this->assertSame(0, $this->offer->fresh()->comments_count);
        $this->app['auth']->forgetGuards();
        $this->get(route('offers.show', $this->offer->slug))->assertDontSee('Great deal, booked my first lesson!');
    }

    public function test_guests_must_give_a_name_and_bots_are_caught(): void
    {
        $this->postJson(route('offers.comment', $this->offer->slug), ['body' => 'No name here'])
            ->assertStatus(422)->assertJsonValidationErrors('author_name');

        $this->postJson(route('offers.comment', $this->offer->slug), [
            'author_name' => 'Bot', 'body' => 'Buy cheap stuff', 'website' => 'http://spam.example',
        ])->assertStatus(422);

        $this->assertSame(0, OfferComment::count());
    }

    public function test_logged_in_users_comment_under_their_account_name_and_html_is_stripped(): void
    {
        $this->actingAs($this->client)->postJson(route('offers.comment', $this->offer->slug), [
            'author_name' => 'Somebody Else',
            'body' => '<script>alert(1)</script>Nice offer',
        ])->assertOk();

        $comment = OfferComment::firstOrFail();
        $this->assertSame($this->client->name, $comment->author_name);
        $this->assertSame($this->client->id, $comment->user_id);
        $this->assertStringNotContainsString('<script>', $comment->body);
    }

    */

    public function test_admin_can_bulk_moderate(): void
    {
        $ids = collect(range(1, 3))->map(fn ($i) => OfferComment::create([
            'offer_id' => $this->offer->id, 'author_name' => "P{$i}", 'body' => "Body {$i}", 'status' => 'pending',
        ])->id)->all();

        $this->actingAs($this->admin)->post(route('admin.offers.comments.bulk'), ['bulk_action' => 'approve', 'ids' => $ids])->assertRedirect();

        $this->assertSame(3, OfferComment::approved()->count());
        $this->assertSame(3, $this->offer->fresh()->comments_count);
    }

    public function test_only_admins_moderate(): void
    {
        $comment = OfferComment::create(['offer_id' => $this->offer->id, 'author_name' => 'X', 'body' => 'Y', 'status' => 'pending']);

        $this->actingAs($this->client)->get(route('admin.offers.comments'))->assertStatus(403);
        $this->actingAs($this->client)->patch(route('admin.offers.comments.approve', $comment))->assertStatus(403);
        $this->assertSame('pending', $comment->fresh()->status);
    }

    /* ------------------------------------------------------------------ */
    /*  Favorites                                                          */
    /* ------------------------------------------------------------------ */

    public function test_a_client_can_save_and_see_favorites(): void
    {
        $this->actingAs($this->client)->postJson(route('offers.favorite', $this->offer->slug))
            ->assertOk()->assertJson(['saved' => true]);

        $this->assertSame(1, $this->offer->fresh()->favorites_count);
        $this->actingAs($this->client)->get(route('offers.favorites'))->assertOk()->assertSee('Engage Offer');

        $this->actingAs($this->client)->postJson(route('offers.favorite', $this->offer->slug))
            ->assertOk()->assertJson(['saved' => false]);
        $this->assertSame(0, $this->offer->fresh()->favorites_count);
    }

    public function test_favorites_are_for_clients_only(): void
    {
        // Guests are sent to log in, then back to the offer.
        $this->postJson(route('offers.favorite', $this->offer->slug))->assertStatus(401)->assertJsonStructure(['login']);
        $this->assertSame(route('offers.show', $this->offer->slug), session('url.intended'));

        $provider = $this->offer->serviceProvider->user;
        $this->actingAs($provider)->postJson(route('offers.favorite', $this->offer->slug))->assertStatus(403);
        $this->actingAs($provider)->get(route('offers.favorites'))->assertStatus(403);
        $this->actingAs($this->admin)->postJson(route('offers.favorite', $this->offer->slug))->assertStatus(403);

        $this->assertSame(0, $this->offer->fresh()->favorites_count);
    }

    public function test_the_save_button_is_shown_to_guests_and_clients_only(): void
    {
        $this->get(route('offers.show', $this->offer->slug))->assertSee(__('offers.save_favorite'));
        $this->actingAs($this->client)->get(route('offers.show', $this->offer->slug))->assertSee(__('offers.save_favorite'));
        $this->actingAs($this->offer->serviceProvider->user)->get(route('offers.show', $this->offer->slug))->assertDontSee(__('offers.save_favorite'));
    }

    /* ------------------------------------------------------------------ */
    /*  WhatsApp                                                           */
    /* ------------------------------------------------------------------ */

    public function test_publish_your_offer_goes_to_speeda_whatsapp(): void
    {
        $expected = 'https://wa.me/16138649118?text=';

        $this->get(route('offers.index'))->assertOk()
            ->assertSee($expected, false)
            ->assertSee(__('offers.contact_to_publish_hint'))
            ->assertDontSee('m.me/', false);

        // The hero is a button only; the number stays in the offer page's publish box.
        $this->get(route('offers.show', $this->offer->slug))
            ->assertSee($expected, false)
            ->assertSee('+1 (613) 864-9118');

        $url = WhatsApp::contactSpeedaUrl();
        $this->assertStringContainsString('publish an offer on SPEEDA', rawurldecode($url));
    }

    public function test_claim_link_dials_the_provider_in_international_format(): void
    {
        $url = $this->get(route('offers.go', $this->offer->slug))->headers->get('Location');

        // Stored as 6135550199 — must become +1, not the +61 WhatsApp would guess.
        $this->assertStringStartsWith('https://wa.me/16135550199?text=', $url);
    }

    /* Temporarily disabled with the public offer comment routes and UI.
    public function test_image_event_handlers_are_removed_before_comment_approval(): void
    {
        $this->postJson(route('offers.comment', $this->offer->slug), [
            'author_name' => 'Sara', 'body' => '<img src=x onerror=alert(1)>Safe offer comment',
        ])->assertOk();
        $comment = OfferComment::firstOrFail();
        $this->assertSame('Safe offer comment', $comment->body);
        $this->get(route('offers.show', $this->offer->slug))->assertDontSee('Safe offer comment');
        $this->actingAs($this->admin)->patch(route('admin.offers.comments.approve', $comment))->assertRedirect();
        $this->get(route('offers.show', $this->offer->slug))->assertSee('Safe offer comment')
            ->assertDontSee('onerror=alert(1)', false);
    }

    public function test_markup_only_comments_are_rejected(): void
    {
        $this->postJson(route('offers.comment', $this->offer->slug), [
            'author_name' => 'Sara', 'body' => '<img src=x onerror=alert(1)>',
        ])->assertUnprocessable()->assertJsonValidationErrors('body');
        $this->assertSame(0, OfferComment::count());
    }

    public function test_daily_comment_limit_survives_a_new_session(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->postJson(route('offers.comment', $this->offer->slug), ['author_name' => 'Sara', 'body' => 'A useful question'])
                ->assertOk();
            $this->travel(61)->seconds();
        }
        $this->withCookie(config('session.cookie'), str_repeat('z', 40));
        $this->postJson(route('offers.comment', $this->offer->slug), ['author_name' => 'Sara', 'body' => 'Another question'])
            ->assertTooManyRequests();
        $this->assertSame(20, OfferComment::count());
    }

    */

    public function test_favorites_navigation_exists_and_hidden_offers_are_not_disclosed(): void
    {
        $this->actingAs($this->client)->postJson(route('offers.favorite', $this->offer->slug))->assertOk();
        $this->get(route('offers.index'))->assertSee(route('offers.favorites'), false);
        $this->offer->update(['is_active' => false]);
        $this->get(route('offers.favorites'))->assertOk()->assertDontSee('Engage Offer');
    }
}
