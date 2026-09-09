<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Services\ProviderEmailJourneyService;
use App\Services\UserEngagementEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The opt-out link now appears in every non-transactional email and stops all
 * of them, not just admin broadcasts.
 *
 * The two things worth pinning down are the pair that usually break: that the
 * link is actually IN the mail (a promise nobody checks until a spam complaint
 * arrives), and that setting the flag genuinely stops the senders rather than
 * only the one that happened to be tested.
 */
class EmailUnsubscribeTest extends TestCase
{
    use RefreshDatabase;

    private function makeProvider(array $userAttributes = []): ServiceProvider
    {
        $location = Location::factory()->create(['city' => 'OptOutCity_' . uniqid()]);
        $category = Category::factory()->create();

        $provider = ServiceProvider::factory()
            ->forLocation($location->id)
            ->forCategory($category->id)
            ->create(['is_active' => true]);

        $provider->user->update($userAttributes + ['is_active' => true]);

        return $provider->fresh('user');
    }

    /* ================================================================== */
    /*  The link is present                                                */
    /* ================================================================== */

    public static function providerMailables(): array
    {
        return [
            'welcome' => [\App\Mail\Provider\WelcomeEmail::class],
            'photo' => [\App\Mail\Provider\AddProfilePhotoEmail::class],
            'services' => [\App\Mail\Provider\AddServicesEmail::class],
            'bio' => [\App\Mail\Provider\WriteDescriptionEmail::class],
            'experience' => [\App\Mail\Provider\AddExperienceEmail::class],
            'gallery' => [\App\Mail\Provider\ShowcaseWorkEmail::class],
            'profile complete' => [\App\Mail\Provider\ProfileCompleteEmail::class],
            'build reputation' => [\App\Mail\Provider\BuildReputationEmail::class],
            'first review received' => [\App\Mail\Provider\FirstReviewReceivedEmail::class],
            'fifth review received' => [\App\Mail\Provider\FifthReviewReceivedEmail::class],
        ];
    }

    #[DataProvider('providerMailables')]
    public function test_every_provider_email_carries_a_signed_opt_out_link(string $mailable): void
    {
        $provider = $this->makeProvider();

        $html = (new $mailable($provider, 'https://speeda.test/p/1'))->render();

        $this->assertStringContainsString('/email/unsubscribe/', $html);
        $this->assertStringContainsString('signature=', $html);
        // Exactly one link: the layout renders it, so an individual template
        // adding its own would silently double it.
        $this->assertSame(1, substr_count($html, '/email/unsubscribe/'));
    }

    public static function clientMailables(): array
    {
        return [
            'welcome' => [\App\Mail\Client\WelcomeClientEmail::class],
            'first review' => [\App\Mail\Client\FirstReviewEmail::class],
            'fifth review' => [\App\Mail\Client\FifthReviewEmail::class],
        ];
    }

    #[DataProvider('clientMailables')]
    public function test_every_client_email_carries_a_signed_opt_out_link(string $mailable): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $html = (new $mailable($user, 'https://speeda.test/browse'))->render();

        $this->assertStringContainsString('/email/unsubscribe/', $html);
        $this->assertSame(1, substr_count($html, '/email/unsubscribe/'));
    }

    public function test_the_one_click_header_is_set_for_gmail_and_outlook(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $mail = new \App\Mail\Client\WelcomeClientEmail($user, 'https://speeda.test/browse');
        $message = (new \Illuminate\Mail\Message(new \Symfony\Component\Mime\Email))->getSymfonyMessage();

        foreach ($mail->envelope()->using as $callback) {
            $callback($message);
        }

        $this->assertNotNull($message->getHeaders()->get('List-Unsubscribe'));
        $this->assertSame(
            'List-Unsubscribe=One-Click',
            $message->getHeaders()->get('List-Unsubscribe-Post')->getBodyAsString(),
        );
    }

    public function test_transactional_mail_carries_no_opt_out_link(): void
    {
        // Losing password-reset mail to an unsubscribe click would lock people
        // out of their own account, so it deliberately has no link.
        $user = User::factory()->create();

        $html = (string) (new \App\Notifications\Auth\ResetPasswordNotification('token'))
            ->toMail($user)
            ->render();

        $this->assertStringNotContainsString('/email/unsubscribe/', $html);
    }

    /**
     * The two data providers above are hand-written lists, so a mailable added
     * later would simply not be tested — and a marketing email that ships
     * without an opt-out link is exactly the thing that gets a sending domain
     * blocked. This walks app/Mail instead of trusting the lists, and fails
     * until the new class is either covered or declared transactional.
     */
    public function test_no_mailable_escapes_the_opt_out_checks(): void
    {
        $declared = array_merge(
            array_column(self::providerMailables(), 0),
            array_column(self::clientMailables(), 0),
            [
                // Carries the link through its own constructor argument, and is
                // covered by ProviderBroadcastTest.
                \App\Mail\Provider\BroadcastEmail::class,
            ],
        );

        $found = [];

        foreach (
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(app_path('Mail'), \FilesystemIterator::SKIP_DOTS)
            ) as $file
        ) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = 'App\\Mail\\' . str_replace(
                [DIRECTORY_SEPARATOR, '.php'],
                ['\\', ''],
                substr($file->getPathname(), strlen(app_path('Mail')) + 1)
            );

            if (! class_exists($class) || (new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            $found[] = $class;
        }

        $this->assertNotEmpty($found, 'No mailables were discovered — the scan is broken, not the mail.');

        $uncovered = array_diff($found, $declared);

        $this->assertSame([], array_values($uncovered), sprintf(
            'These mailables are not covered by an opt-out test: %s. Add them to providerMailables()/clientMailables(), '
            . 'or to the transactional list here if the mail is account-critical.',
            implode(', ', $uncovered)
        ));
    }

    /* ================================================================== */
    /*  The flag actually stops mail                                       */
    /* ================================================================== */

    public function test_an_opted_out_provider_gets_no_journey_email(): void
    {
        Mail::fake();

        $provider = $this->makeProvider(['email_opt_out_at' => now()]);

        app(ProviderEmailJourneyService::class)->processProvider($provider);
        app(ProviderEmailJourneyService::class)->sendWelcomeEmail($provider);

        Mail::assertNothingQueued();
    }

    public function test_an_opted_out_client_gets_no_welcome_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'client',
            'email_opt_out_at' => now(),
        ]);

        app(UserEngagementEmailService::class)->sendClientWelcomeEmail($user);

        Mail::assertNothingQueued();

        // The send-once flag must stay clear, so resubscribing later still
        // gets them the welcome email rather than silently swallowing it.
        $this->assertNull($user->fresh()->client_welcome_email_sent_at);
    }

    public function test_a_subscribed_provider_still_gets_the_welcome_email(): void
    {
        Mail::fake();

        $provider = $this->makeProvider();

        app(ProviderEmailJourneyService::class)->sendWelcomeEmail($provider);

        Mail::assertQueued(\App\Mail\Provider\WelcomeEmail::class);
    }
}
