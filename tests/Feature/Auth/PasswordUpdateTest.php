<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\Auth\PasswordChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'SecureReset123!',
                'password_confirmation' => 'SecureReset123!',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('SecureReset123!', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'SecureReset123!',
                'password_confirmation' => 'SecureReset123!',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }

    public function test_owner_is_emailed_when_password_changes(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'password',
            'password' => 'SecureReset123!',
            'password_confirmation' => 'SecureReset123!',
        ]);

        Notification::assertSentTo($user, PasswordChangedNotification::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);

            return $mail->subject === __('auth.password_changed_email_subject')
                && $mail->actionUrl === route('password.request');
        });
    }

    public function test_no_email_when_current_password_is_wrong(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'SecureReset123!',
            'password_confirmation' => 'SecureReset123!',
        ]);

        Notification::assertNothingSent();
    }

    public function test_admin_can_reach_the_change_password_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('profile.edit'), false)
            ->assertSee('Change Password');

        // The delete-account form is refused for admins, so it is not shown to them.
        $this->actingAs($admin)->get('/profile')
            ->assertOk()
            ->assertSee(route('password.update'), false)
            ->assertDontSee('id="delete-title"', false);
    }
}
