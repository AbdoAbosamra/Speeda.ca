<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The single, permanent platform administrator.
 *
 * Login identifier `admin` resolves to this account's email via
 * config('auth.admin_email') (see App\Http\Requests\Auth\LoginRequest), so the
 * fixed credentials are:
 *
 *     login:    admin
 *     password: admin12345678910
 *
 * This seeder is idempotent and safe to run on every deploy — local AND
 * production. It guarantees the admin exists with these exact credentials and
 * never touches anything else. The plain password below is auto-hashed by the
 * User model's 'hashed' cast.
 */
class AdminUserSeeder extends Seeder
{
    public const LOGIN = 'admin';

    public const PASSWORD = 'admin12345678910';

    public function run(): void
    {
        $email = config('auth.admin_email', 'admin@speeda.com');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => self::PASSWORD, // auto-hashed by the 'hashed' cast
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info('   ✅ Admin ensured — login `'.self::LOGIN."` / email {$email}");
    }
}
