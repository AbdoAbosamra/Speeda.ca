<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\Offer;
use App\Models\OfferSection;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * LOCAL ONLY demo accounts for trying profile completion and provider offers.
 *
 *     php artisan db:seed --class=LocalDemoSeeder
 *
 * Every account uses the password below and an @speeda.test email, so none of
 * them can be mistaken for a real person. Safe to run again: it updates the
 * same accounts instead of adding new ones. Refuses to run outside APP_ENV=local.
 */
class LocalDemoSeeder extends Seeder
{
    public const PASSWORD = 'SpeedaDemo#2026';

    /** email => [name, role, what it is for] — also printed after seeding. */
    public const ACCOUNTS = [
        'demo-admin@speeda.test' => ['Demo Admin', 'admin', 'Admin panel: dashboard stats, offer review (approve / reject)'],
        'demo-client@speeda.test' => ['Demo Client', 'client', 'Visitor account: open /offers/create to try "register as a provider"'],
        'demo-pro-100@speeda.test' => ['Demo Pro Full', 'service_provider', 'Photo + experience + description: can create an offer; has live, pending and rejected offers'],
        'demo-pro-67@speeda.test' => ['Demo Pro Two Thirds', 'service_provider', 'Photo + experience, no description: offer flow asks for the description'],
        'demo-pro-33@speeda.test' => ['Demo Pro One Third', 'service_provider', 'Photo only: checklist shows experience and description missing'],
        'demo-pro-0@speeda.test' => ['Demo Pro Empty', 'service_provider', 'Brand-new provider: nothing filled in yet'],
    ];

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->error('LocalDemoSeeder only runs with APP_ENV=local. Nothing was changed.');

            return;
        }

        $category = Category::query()->where('is_active', true)->inRandomOrder()->first() ?? Category::query()->first();
        $location = Location::query()->where('is_active', true)->where('city', 'Ottawa')->first()
            ?? Location::query()->where('is_active', true)->first()
            ?? Location::query()->first();

        if (! $category || ! $location) {
            $this->command?->error('Seed categories and locations first (php artisan db:seed). Nothing was changed.');

            return;
        }

        $photo = $this->demoPhoto();
        $bio = 'Licensed professional serving Ottawa and Gatineau. Free quotes, clean work and on-time visits.';

        $users = [];
        foreach (self::ACCOUNTS as $email => [$name, $role]) {
            $users[$email] = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => self::PASSWORD, // hashed by the User model cast
                'role' => $role,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $profiles = [
            'demo-pro-100@speeda.test' => ['photo' => $photo, 'experience' => 8, 'bio' => $bio, 'phone' => '+16135550101'],
            'demo-pro-67@speeda.test' => ['photo' => $photo, 'experience' => 3, 'bio' => ServiceProvider::PLACEHOLDER_BIO, 'phone' => '+16135550102'],
            'demo-pro-33@speeda.test' => ['photo' => $photo, 'experience' => null, 'bio' => ServiceProvider::PLACEHOLDER_BIO, 'phone' => '+16135550103'],
            'demo-pro-0@speeda.test' => ['photo' => null, 'experience' => null, 'bio' => ServiceProvider::PLACEHOLDER_BIO, 'phone' => '+16135550104'],
        ];

        $providers = [];
        foreach ($profiles as $email => $p) {
            $user = $users[$email];
            $providers[$email] = ServiceProvider::updateOrCreate(['user_id' => $user->id], [
                'company_name' => $user->name,
                'category_id' => $category->id,
                'location_id' => $location->id,
                'phone' => $p['phone'],
                'whatsapp_number' => $p['phone'],
                'profile_image' => $p['photo'],
                'experience_years' => $p['experience'],
                'bio' => $p['bio'],
                'is_active' => true,
            ]);
        }

        $this->seedOffers($providers['demo-pro-100@speeda.test'], $users['demo-admin@speeda.test']);

        $this->command?->info('Local demo accounts ready. Password for all: '.self::PASSWORD);
        foreach (self::ACCOUNTS as $email => [$name, $role]) {
            $pct = isset($providers[$email]) ? ' ('.$providers[$email]->fresh()->profile_completion_percent.'%)' : '';
            $this->command?->line("  {$role}{$pct}: {$email}");
        }
    }

    /** A real image on the public disk, so the photo counts as present. */
    private function demoPhoto(): string
    {
        $path = 'profiles/local-demo.png';
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, File::get(public_path('images/user.png')));
        }

        return $path;
    }

    private function seedOffers(ServiceProvider $provider, User $admin): void
    {
        OfferSection::current()->update(['is_active' => true]);

        $base = [
            'service_provider_id' => $provider->id,
            'deal_type' => Offer::TYPE_PERCENT,
            'cta_type' => Offer::CTA_WHATSAPP,
            'starts_at' => today()->subDay(),
            'ends_at' => today()->addDays(30),
            'created_by' => $provider->user_id,
            'updated_by' => $provider->user_id,
        ];

        $offers = [
            'local-demo-live' => [
                'deal_value' => 20, 'title_en' => 'Demo: 20% off your first visit', 'title_ar' => 'تجربة: خصم 20% على أول زيارة',
                'summary_en' => 'Approved demo offer, shown on the home page below Explore.', 'summary_ar' => 'إعلان تجريبي معتمد يظهر في الصفحة الرئيسية تحت Explore.',
                'is_active' => true, 'is_featured' => true,
                'review_status' => Offer::REVIEW_APPROVED, 'submitted_at' => now()->subDays(2), 'reviewed_at' => now()->subDay(), 'reviewed_by' => $admin->id,
            ],
            'local-demo-pending' => [
                'deal_value' => 15, 'title_en' => 'Demo: 15% off kitchen repairs', 'title_ar' => 'تجربة: خصم 15% على تصليح المطابخ',
                'summary_en' => 'Waiting for admin review — approve or reject it from the admin panel.', 'summary_ar' => 'قيد المراجعة — وافق عليه أو ارفضه من لوحة الأدمن.',
                'is_active' => false, 'is_featured' => false,
                'review_status' => Offer::STATUS_PENDING, 'submitted_at' => now()->subHours(3), 'reviewed_at' => null, 'reviewed_by' => null,
            ],
            'local-demo-rejected' => [
                'deal_value' => 90, 'title_en' => 'Demo: 90% off everything', 'title_ar' => 'تجربة: خصم 90% على كل شيء',
                'summary_en' => 'A rejected demo offer, with the reason shown to the provider.', 'summary_ar' => 'إعلان تجريبي مرفوض ويظهر سبب الرفض لمقدّم الخدمة.',
                'is_active' => false, 'is_featured' => false,
                'review_status' => Offer::STATUS_REJECTED, 'submitted_at' => now()->subDays(3), 'reviewed_at' => now()->subDays(2), 'reviewed_by' => $admin->id,
                'rejection_reason' => 'The discount looks unrealistic. Please send a real offer.',
            ],
        ];

        foreach ($offers as $slug => $attrs) {
            $offer = Offer::withTrashed()->firstOrNew(['slug' => $slug]);
            $offer->forceFill(array_merge($base, ['rejection_reason' => null], $attrs, [
                'slug' => $slug,
                'sort_order' => $offer->sort_order ?: Offer::nextSortOrder(),
                'deleted_at' => null,
            ]))->save();
        }
    }
}
