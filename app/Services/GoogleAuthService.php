<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialiteUser;

/**
 * Everything that happens between "Google says who this is" and "we have a
 * local account", kept out of the controller so the rules are testable
 * without driving a browser through Google.
 *
 * The rule that matters: an account is matched by Google's subject id first,
 * and only then by email. The subject id is permanent; an email address is
 * not, and matching on it alone is how one person ends up signed into
 * somebody else's account after an address is released and re-registered.
 */
class GoogleAuthService
{
    /** How long a half-finished provider signup may sit in the session. */
    public const PENDING_TTL_MINUTES = 30;

    public const SESSION_PENDING = 'google_auth.pending';

    public const SESSION_INTENT = 'google_auth.intent';

    public function __construct(private readonly AuthService $authService) {}

    /**
     * Whether the feature is configured at all. Without credentials the
     * buttons are hidden and the routes abort, so a deployment that has not
     * been given keys yet shows nothing rather than a Google error screen.
     */
    public static function isEnabled(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    /**
     * An existing account for this Google identity, or null if they are new.
     *
     * Links the Google id to a pre-existing password account on first sign-in,
     * so someone who registered with a password and later clicks "Continue
     * with Google" lands in the same account instead of a duplicate.
     */
    public function findOrLinkExisting(SocialiteUser $googleUser): ?User
    {
        $byGoogleId = User::where('google_id', $googleUser->getId())->first();

        if ($byGoogleId) {
            return $byGoogleId;
        }

        $byEmail = User::where('email', $googleUser->getEmail())->first();

        if (!$byEmail) {
            return null;
        }

        $byEmail->forceFill([
            'google_id' => $googleUser->getId(),
            'google_linked_at' => now(),
            // Google has verified this address for us; an account that was
            // sitting unverified is verified by the act of proving it.
            'email_verified_at' => $byEmail->email_verified_at ?? now(),
        ])->save();

        Log::info('[GoogleAuth] Linked Google identity to existing account', [
            'user_id' => $byEmail->id,
        ]);

        return $byEmail;
    }

    /**
     * Create the account once the second form has supplied what Google could
     * not: which role they are signing up as, and, for a provider, the trade,
     * city and phone their public profile cannot exist without.
     *
     * Reuses AuthService for both roles so an account created this way is
     * identical to one created through the normal form — same ServiceProvider
     * record, same welcome email, same location handling.
     */
    public function createAccount(array $pending, array $details): User
    {
        $role = ($details['role'] ?? 'client') === 'service_provider'
            ? 'service_provider'
            : 'client';

        $payload = [
            'name' => $details['name'],
            'email' => $pending['email'],
            'role' => $role,
            'password' => null,
            'google_id' => $pending['google_id'],
        ];

        if ($role === 'service_provider') {
            $payload += [
                'profession' => $details['profession'],
                'city' => $details['city'],
                'mobile' => $details['mobile'],
                'whatsapp_number' => $details['whatsapp_number'] ?? null,
            ];
        }

        $user = $this->authService->registerUser($payload);

        Log::info('[GoogleAuth] Created account from Google signup', [
            'user_id' => $user->id,
            'role' => $role,
        ]);

        return $user;
    }

    /**
     * The Google profile, reduced to what the second step needs. Only these
     * fields go into the session — never the access token.
     *
     * The intent rides along so the second form can pre-select the role the
     * button implied; it is a default for the form, not a decision, because
     * the submitted role is what actually creates the account.
     */
    public function pendingPayload(SocialiteUser $googleUser, string $intent = 'client'): array
    {
        return [
            'google_id' => $googleUser->getId(),
            'email' => $googleUser->getEmail(),
            'name' => $this->resolveName($googleUser),
            'role' => $intent === 'service_provider' ? 'service_provider' : 'client',
            'created_at' => now()->timestamp,
        ];
    }

    public function pendingIsFresh(?array $pending): bool
    {
        return is_array($pending)
            && isset($pending['google_id'], $pending['email'], $pending['created_at'])
            && $pending['created_at'] > now()->subMinutes(self::PENDING_TTL_MINUTES)->timestamp;
    }

    /**
     * Google may return an account with no display name set.
     */
    private function resolveName(SocialiteUser $googleUser): string
    {
        $name = trim((string) $googleUser->getName());

        if ($name !== '') {
            return $name;
        }

        $prefix = \Illuminate\Support\Str::before((string) $googleUser->getEmail(), '@');
        $cleaned = trim(str_replace(['.', '_', '-'], ' ', $prefix));

        return $cleaned !== '' ? \Illuminate\Support\Str::title($cleaned) : 'Speeda User';
    }
}
