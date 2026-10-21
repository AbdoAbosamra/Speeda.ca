<?php

namespace Tests\Feature;

use App\Models\HomeVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The admin-managed video section on the home page.
 *
 * Two things carry the most risk and get the most cover here. First, the
 * section must be genuinely absent — not merely empty — whenever it is off or
 * unconfigured, because an empty black frame on the home page is worse than
 * no section at all. Second, the pasted URL reaches an iframe src, so it is
 * parsed into a known video id rather than trusted.
 */
class HomeVideoTest extends TestCase
{
    use RefreshDatabase;

    private const YT = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

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
    /*  URL parsing                                                        */
    /* ================================================================== */

    public static function youtubeUrls(): array
    {
        return [
            'watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'watch with timestamp' => ['https://youtube.com/watch?v=dQw4w9WgXcQ&t=42s', 'dQw4w9WgXcQ'],
            'short link' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'short link with tracking' => ['https://youtu.be/dQw4w9WgXcQ?si=abc123', 'dQw4w9WgXcQ'],
            'embed' => ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'mobile' => ['https://m.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
        ];
    }

    #[DataProvider('youtubeUrls')]
    public function test_it_reads_the_video_id_from_every_shape_of_youtube_link(string $url, string $expected): void
    {
        $this->assertSame($expected, HomeVideo::parseYoutubeId($url));
    }

    public static function hostileUrls(): array
    {
        return [
            'javascript scheme' => ['javascript:alert(1)'],
            'lookalike domain' => ['https://youtube.com.evil.example/watch?v=dQw4w9WgXcQ'],
            'unrelated host' => ['https://evil.example/watch?v=dQw4w9WgXcQ'],
            'id too short' => ['https://www.youtube.com/watch?v=short'],
            'empty' => [''],
        ];
    }

    #[DataProvider('hostileUrls')]
    public function test_it_refuses_anything_that_is_not_a_real_youtube_link(string $url): void
    {
        $this->assertNull(HomeVideo::parseYoutubeId($url));

        $video = new HomeVideo(['source_type' => HomeVideo::SOURCE_EMBED, 'video_url' => $url]);
        $this->assertNull($video->embedUrl(), 'An unrecognised URL must never reach an iframe src.');
    }

    public function test_it_reads_vimeo_links(): void
    {
        $this->assertSame('123456789', HomeVideo::parseVimeoId('https://vimeo.com/123456789'));
        $this->assertSame('123456789', HomeVideo::parseVimeoId('https://player.vimeo.com/video/123456789'));
        $this->assertNull(HomeVideo::parseVimeoId('https://evil.example/123456789'));
    }

    public function test_the_youtube_embed_url_avoids_tracking_cookies_until_play(): void
    {
        $video = new HomeVideo(['source_type' => HomeVideo::SOURCE_EMBED, 'video_url' => self::YT]);

        $this->assertStringStartsWith('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $video->embedUrl());
    }

    /* ================================================================== */
    /*  When the section appears                                           */
    /* ================================================================== */

    public function test_the_home_page_has_no_video_section_before_anything_is_configured(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('home-video-section');
    }

    public function test_the_section_stays_hidden_while_switched_off(): void
    {
        HomeVideo::create([
            'is_active' => false,
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => self::YT,
            'title_en' => 'Meet Speeda',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('home-video-section')
            ->assertDontSee('Meet Speeda');
    }

    public function test_the_section_stays_hidden_when_switched_on_with_no_video(): void
    {
        // The trap this guards: an admin enables the section before pasting a
        // URL and an empty black frame ships to the live home page.
        HomeVideo::create([
            'is_active' => true,
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => null,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('home-video-section');
    }

    public function test_the_section_stays_hidden_when_the_stored_url_is_not_playable(): void
    {
        HomeVideo::create([
            'is_active' => true,
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => 'https://evil.example/watch?v=dQw4w9WgXcQ',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('home-video-section');
    }

    public function test_the_section_appears_once_it_is_on_and_playable(): void
    {
        HomeVideo::create([
            'is_active' => true,
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => self::YT,
            'title_en' => 'Meet Speeda',
            'description_en' => 'A minute on how it works.',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-video-section')
            ->assertSee('Meet Speeda')
            ->assertSee('A minute on how it works.');
    }

    public function test_the_video_sits_after_explore_and_above_the_coverage_map(): void
    {
        HomeVideo::create([
            'is_active' => true,
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => self::YT,
        ]);

        $this->get(route('home'))->assertOk()->assertSeeInOrder([
            __('home.explore_services'),
            'home-video-section',
            'coverage-map-card',
        ], false);
    }

    public function test_the_iframe_is_not_in_the_markup_until_the_visitor_presses_play(): void
    {
        HomeVideo::create([
            'is_active' => true,
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => self::YT,
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        // The embed URL ships as a data attribute for the click handler; an
        // <iframe> on load would pull a megabyte of YouTube script into the
        // home page's critical path.
        $this->assertStringContainsString('data-home-video-embed', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    public function test_an_uploaded_video_is_rendered_without_preloading_it(): void
    {
        HomeVideo::create([
            'is_active' => true,
            'source_type' => HomeVideo::SOURCE_UPLOAD,
            'video_path' => 'home-video/clip.mp4',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-video-section')
            ->assertSee('preload="none"', false);
    }

    /* ================================================================== */
    /*  Localised copy                                                     */
    /* ================================================================== */

    public function test_copy_follows_the_visitors_language(): void
    {
        HomeVideo::create([
            'is_active' => true,
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => self::YT,
            'title_en' => 'Meet Speeda',
            'title_ar' => 'تعرّف على سبيدا',
        ]);

        app()->setLocale('ar');
        $this->assertSame('تعرّف على سبيدا', HomeVideo::forHomePage()->localized_title);

        app()->setLocale('en');
        $this->assertSame('Meet Speeda', HomeVideo::forHomePage()->localized_title);
    }

    public function test_a_missing_translation_falls_back_instead_of_rendering_blank(): void
    {
        HomeVideo::create([
            'is_active' => true,
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => self::YT,
            'title_en' => 'Meet Speeda',
        ]);

        app()->setLocale('fr');

        $this->assertSame('Meet Speeda', HomeVideo::forHomePage()->localized_title);
    }

    /* ================================================================== */
    /*  Admin screen                                                       */
    /* ================================================================== */

    public function test_a_non_admin_cannot_reach_the_editor(): void
    {
        $this->get(route('admin.home_video.edit'))->assertRedirect();

        $this->actingAs(User::factory()->create(['role' => 'client']))
            ->get(route('admin.home_video.edit'))
            ->assertForbidden();
    }

    public function test_the_editor_opens_and_creates_the_record_on_first_visit(): void
    {
        $this->assertDatabaseCount('home_videos', 0);

        $this->actingAs($this->admin)
            ->get(route('admin.home_video.edit'))
            ->assertOk();

        $this->assertDatabaseCount('home_videos', 1);
    }

    public function test_admin_can_save_a_youtube_link_and_copy(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.home_video.update'), [
                'is_active' => '1',
                'source_type' => HomeVideo::SOURCE_EMBED,
                'video_url' => self::YT,
                'title_en' => 'Meet Speeda',
                'title_ar' => 'تعرّف على سبيدا',
                'description_en' => 'A minute on how it works.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.home_video.edit'));

        $video = HomeVideo::firstOrFail();

        $this->assertTrue($video->is_active);
        $this->assertSame(self::YT, $video->video_url);
        $this->assertSame('Meet Speeda', $video->title_en);
        $this->assertSame($this->admin->id, $video->updated_by);
        $this->assertTrue($video->isPlayable());
    }

    public function test_an_unrecognised_link_is_rejected_with_a_useful_message(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.home_video.update'), [
                'is_active' => '1',
                'source_type' => HomeVideo::SOURCE_EMBED,
                'video_url' => 'https://example.com/some-video',
            ])
            ->assertSessionHasErrors('video_url');

        $this->assertNull(HomeVideo::first()?->video_url);
    }

    public function test_admin_can_switch_the_section_off_without_losing_the_video(): void
    {
        $this->actingAs($this->admin)->put(route('admin.home_video.update'), [
            'is_active' => '1',
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => self::YT,
        ]);

        $this->actingAs($this->admin)->put(route('admin.home_video.update'), [
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => self::YT,
        ])->assertSessionHasNoErrors();

        $video = HomeVideo::firstOrFail();
        $this->assertFalse($video->is_active);
        $this->assertSame(self::YT, $video->video_url, 'Turning the section off must not discard the link.');

        $this->get(route('home'))->assertDontSee('home-video-section');
    }

    public function test_admin_can_upload_a_poster_image(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.home_video.update'), [
                'is_active' => '1',
                'source_type' => HomeVideo::SOURCE_EMBED,
                'video_url' => self::YT,
                'poster' => UploadedFile::fake()->image('cover.jpg', 1280, 720),
            ])
            ->assertSessionHasNoErrors();

        $video = HomeVideo::firstOrFail();

        $this->assertNotNull($video->poster_path);
        Storage::disk('public')->assertExists($video->poster_path);
    }

    public function test_replacing_the_poster_deletes_the_previous_file(): void
    {
        $video = HomeVideo::current();
        $video->forceFill(['poster_path' => 'home-video/posters/old.jpg'])->save();
        Storage::disk('public')->put('home-video/posters/old.jpg', 'fake');

        $this->actingAs($this->admin)
            ->put(route('admin.home_video.update'), [
                'source_type' => HomeVideo::SOURCE_EMBED,
                'video_url' => self::YT,
                'poster' => UploadedFile::fake()->image('new-cover.jpg'),
            ])
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('home-video/posters/old.jpg');
    }

    public function test_admin_can_remove_the_uploaded_video_file(): void
    {
        $video = HomeVideo::current();
        $video->forceFill(['video_path' => 'home-video/clip.mp4'])->save();
        Storage::disk('public')->put('home-video/clip.mp4', 'fake');

        $this->actingAs($this->admin)
            ->delete(route('admin.home_video.destroy_file'))
            ->assertRedirect(route('admin.home_video.edit'));

        Storage::disk('public')->assertMissing('home-video/clip.mp4');
        $this->assertNull(HomeVideo::firstOrFail()->video_path);
    }

    public function test_a_non_video_file_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.home_video.update'), [
                'source_type' => HomeVideo::SOURCE_UPLOAD,
                'video_file' => UploadedFile::fake()->create('payload.php', 40, 'application/x-php'),
            ])
            ->assertSessionHasErrors('video_file');

        $this->assertNull(HomeVideo::first()?->video_path);
    }

    public function test_the_youtube_thumbnail_stands_in_when_no_poster_is_uploaded(): void
    {
        $video = new HomeVideo([
            'source_type' => HomeVideo::SOURCE_EMBED,
            'video_url' => self::YT,
        ]);

        $this->assertStringContainsString('dQw4w9WgXcQ', (string) $video->displayPosterUrl());
    }
}
