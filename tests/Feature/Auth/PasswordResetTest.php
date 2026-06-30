<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'SecureReset123!',
                'password_confirmation' => 'SecureReset123!',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            $this->assertTrue(Hash::check('SecureReset123!', $user->refresh()->password));

            return true;
        });
    }

    public function test_unknown_email_gets_generic_response_without_notification(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'missing@example.com']);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', __(Password::RESET_LINK_SENT));

        Notification::assertNothingSent();
    }

    public function test_password_reset_requests_are_rate_limited(): void
    {
        Notification::fake();

        config([
            'auth.password_reset.rate_limit_per_minute' => 2,
            'auth.password_reset.rate_limit_per_hour' => 1000,
        ]);

        $server = ['REMOTE_ADDR' => '203.0.113.10'];

        $this->withServerVariables($server)
            ->post('/forgot-password', ['email' => 'limited@example.com'])
            ->assertRedirect();

        $this->withServerVariables($server)
            ->post('/forgot-password', ['email' => 'limited@example.com'])
            ->assertRedirect();

        $this->withServerVariables($server)
            ->post('/forgot-password', ['email' => 'limited@example.com'])
            ->assertTooManyRequests();
    }

    public function test_password_reset_email_uses_speeda_support_sender(): void
    {
        Notification::fake();

        config([
            'mail.from.address' => 'support@speeda.ca',
            'mail.from.name' => 'Speeda Support',
            'auth.password_reset.support_email' => 'support@speeda.ca',
        ]);

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $mail = $notification->toMail($user);

            $this->assertSame(['support@speeda.ca', 'Speeda Support'], $mail->from);
            $this->assertSame([['support@speeda.ca', 'Speeda Support']], $mail->replyTo);
            $this->assertSame('Reset your Speeda password', $mail->subject);
            $this->assertStringContainsString('/reset-password/'.$notification->token, $mail->actionUrl);
            $this->assertStringContainsString('email='.urlencode($user->email), $mail->actionUrl);

            return true;
        });
    }

    public function test_resetting_password_removes_existing_database_sessions(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'existing-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'SecureReset123!',
                'password_confirmation' => 'SecureReset123!',
            ])->assertSessionHasNoErrors();

            return true;
        });

        $this->assertDatabaseMissing('sessions', [
            'id' => 'existing-session-id',
        ]);
    }

    public function test_password_reset_requires_a_strong_new_password(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertSessionHasErrors('password');

            return true;
        });
    }
}
