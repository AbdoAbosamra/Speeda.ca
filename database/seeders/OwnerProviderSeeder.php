<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceArea;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the site owner's service-provider account.
 *
 * Idempotent: safe to run repeatedly (locally and on production) via
 *   php artisan db:seed --class=OwnerProviderSeeder
 *
 * Details:
 * - Primary location is a hidden "Egypt" location (is_active = false) so it never
 *   appears in public city dropdowns/filters and is masked on cards for visitors.
 * - Service areas are added for every active location so the provider shows up as
 *   "available in this area" in every city filter.
 * - Uses the owner's Egyptian phone number, which is whitelisted in config/owner.php.
 */
class OwnerProviderSeeder extends Seeder
{
    private const OWNER_EMAIL   = 'abdo.abosamra80@gmail.com';
    private const OWNER_NAME    = 'Abdelrahman Abosamra';
    private const OWNER_PHONE   = '+201289121218';
    private const OWNER_PASSWORD = 'Ab202020@';
    // Category is resolved by name at run time (IDs differ between environments).
    private const CATEGORY_NAME = 'Web Development';
    private const LINKEDIN_URL  = 'https://www.linkedin.com/in/abdelrahman-abo-samra/';

    public function run(): void
    {
        DB::transaction(function () {
            // 1) Hidden "Egypt" primary location — never shown in public filters.
            $egypt = Location::firstOrNew(['city' => 'Egypt']);
            $egypt->country   = 'Egypt';
            $egypt->is_active = false;
            $egypt->save();

            // 2) Owner user account (service provider).
            $user = User::withTrashed()->firstOrNew(['email' => self::OWNER_EMAIL]);
            if (method_exists($user, 'trashed') && $user->trashed()) {
                $user->restore();
            }
            $user->name              = self::OWNER_NAME;
            $user->role              = 'service_provider';
            $user->profession        = 'Web Development';
            $user->password          = self::OWNER_PASSWORD; // hashed via model cast
            $user->is_active         = true;
            $user->is_service_provider = true;
            $user->is_profile_complete = true;
            $user->provider_status   = 'approved';
            $user->company_name      = self::OWNER_NAME;
            $user->website           = self::LINKEDIN_URL;
            $user->location_id       = $egypt->id;
            $user->social_media_links = json_encode(['linkedin' => self::LINKEDIN_URL]);
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->save();

            // 3) Service provider profile.
            $bioEn = 'Web developer & designer and software engineer. I build fast, '
                . 'modern, responsive websites and web applications — from landing pages '
                . 'and business sites to full custom platforms — with clean UI/UX and SEO '
                . 'best practices. Available remotely for clients everywhere.';

            // Resolve the category by name — IDs are not stable across environments.
            $category = Category::where('name', self::CATEGORY_NAME)->first()
                ?? Category::where('name', 'like', '%Web%')->first()
                ?? Category::where('name', 'like', '%Design%')->first();

            if (! $category) {
                $this->command?->warn('OwnerProviderSeeder: no matching category found; leaving category empty.');
            }

            $provider = ServiceProvider::firstOrNew(['user_id' => $user->id]);
            $provider->category_id       = $category?->id;
            $provider->location_id       = $egypt->id;
            $provider->company_name      = self::OWNER_NAME;
            $provider->bio               = $bioEn;
            $provider->phone             = self::OWNER_PHONE;
            $provider->whatsapp_number   = $provider->whatsapp_number ?: self::OWNER_PHONE;
            $provider->experience_years  = $provider->experience_years ?: 5;
            $provider->languages         = ['English', 'Arabic'];
            $provider->specializations   = [
                'Web Design', 'Web Development', 'Responsive Design',
                'Landing Pages', 'UI/UX', 'SEO',
            ];
            $provider->services_offered  = [
                'Website Design', 'Web Development', 'Web Applications',
                'Landing Pages', 'Website Redesign', 'Website Maintenance',
            ];
            $provider->is_verified       = true;
            $provider->views             = $provider->views ?: 0;
            $provider->rating            = $provider->rating ?: 0;
            $provider->save();

            // 4) Available everywhere: one active service area per active location.
            $activeLocationIds = Location::where('is_active', true)->pluck('id');
            foreach ($activeLocationIds as $locationId) {
                ServiceArea::updateOrCreate(
                    [
                        'service_provider_id' => $provider->id,
                        'location_id'         => $locationId,
                    ],
                    ['is_active' => true]
                );
            }

            $this->command?->info("Owner provider seeded: user #{$user->id}, provider #{$provider->id}, "
                . count($activeLocationIds) . ' service areas.');
        });
    }
}
