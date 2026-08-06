<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-editable copy for an automated email.
 *
 * resolve() is the single entry point used by the Mailables and the preview:
 * it returns the admin's saved copy when a row is active, otherwise the
 * built-in default below. Callers never need to know which one they got.
 *
 * Placeholders use a plain {{ name }} syntax (NOT Blade) and are substituted
 * with escaped values, so an admin can never inject markup or execute code.
 */
class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'subject',
        'badge',
        'headline',
        'lead',
        'next_step_label',
        'next_step_title',
        'next_step_desc',
        'why_label',
        'why_text',
        'cta_label',
        'cta_subtext',
        'quote',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    private const CACHE_KEY = 'email_templates_overrides';

    /** Editable content fields, in form order. */
    public const CONTENT_FIELDS = [
        'subject', 'badge', 'headline', 'lead',
        'next_step_label', 'next_step_title', 'next_step_desc',
        'why_label', 'why_text',
        'cta_label', 'cta_subtext',
        'quote',
    ];

    /** Fields rendered as multi-line textareas in the admin form. */
    public const LONG_FIELDS = ['lead', 'next_step_desc', 'why_text', 'quote'];

    /**
     * Placeholders an admin may use, with a human description.
     */
    public const PLACEHOLDERS = [
        'provider_name' => 'The provider / recipient name',
        'dashboard_url' => 'Link to the recipient dashboard',
        'site_name' => 'Speeda',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* =====================================================================
     |  RESOLUTION
     * ===================================================================== */

    /**
     * Final content for an email key, with placeholders substituted.
     *
     * @param  array<string,string>  $vars
     * @return array<string,string>
     */
    public static function resolve(string $key, array $vars = []): array
    {
        $content = array_merge(
            self::defaults()[$key] ?? [],
            self::override($key)
        );

        return self::substitute($content, $vars);
    }

    /**
     * The saved override for a key, or an empty array when none is active.
     *
     * @return array<string,string>
     */
    public static function override(string $key): array
    {
        $overrides = Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
            return static::query()
                ->where('is_active', true)
                ->get()
                ->keyBy('key')
                ->map(fn ($row) => collect($row->only(self::CONTENT_FIELDS))
                    ->filter(fn ($v) => $v !== null && $v !== '')
                    ->all())
                ->all();
        });

        return $overrides[$key] ?? [];
    }

    /**
     * Replace {{ placeholder }} tokens.
     *
     * Returns PLAIN TEXT, not HTML: the mail templates echo it with Blade's
     * escaping `{{ }}`, so admin copy and substituted values are escaped once
     * at render time. Nothing an admin types can become markup.
     *
     * @param  array<string,string|null>  $content
     * @param  array<string,string>  $vars
     * @return array<string,string>
     */
    public static function substitute(array $content, array $vars): array
    {
        $vars = array_merge(['site_name' => config('app.name', 'Speeda')], $vars);

        $replace = [];
        foreach ($vars as $name => $value) {
            $replace['{{' . $name . '}}'] = (string) $value;
            $replace['{{ ' . $name . ' }}'] = (string) $value;
        }

        return collect($content)
            ->map(fn ($value) => is_string($value) ? strtr($value, $replace) : $value)
            ->all();
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::forgetCache());
        static::deleted(fn () => self::forgetCache());
    }

    /* =====================================================================
     |  BUILT-IN DEFAULTS
     |  Extracted from the original Blade templates so nothing is lost when a
     |  row is deleted or deactivated.
     * ===================================================================== */

    /**
     * @return array<string, array<string,string>>
     */
    public static function defaults(): array
    {
        return [
            'welcome' => [
                'label' => '🎉 Welcome',
                'group' => 'provider',
                'subject' => '🎉 Welcome to Speeda! Your Profile Is Now Live',
                'badge' => "🎉 You're In!",
                'headline' => 'Welcome to Speeda, {{ provider_name }}!',
                'lead' => "We're thrilled to welcome you to our growing network of top-rated local service providers! Your profile is now officially live, opening the doors for local customers to discover and connect with your business.",
                'next_step_label' => 'Your Next Move',
                'next_step_title' => 'Visit Your Dashboard & Claim Your First Step',
                'next_step_desc' => "To help you succeed, we've designed a quick step-by-step path to optimize your profile. Let's make sure you stand out from day one!",
                'why_label' => '',
                'why_text' => '',
                'cta_label' => 'Open My Dashboard',
                'cta_subtext' => 'Takes less than 30 seconds to begin',
                'quote' => '"Your online presence is your modern storefront. A complete profile builds customer confidence before they even contact you."',
            ],

            'photo' => [
                'label' => '📸 Profile Photo',
                'group' => 'provider',
                'subject' => '📸 One Photo Can Change Everything – Add Yours Today',
                'badge' => 'Step 1 of 6',
                'headline' => 'One Photo Can Change Everything 📸',
                'lead' => "Welcome back, {{ provider_name }}! Your profile is live, but it's missing the single most powerful trust builder: your professional photo. Customers want to see the face behind the business before they reach out.",
                'next_step_label' => 'Action Item',
                'next_step_title' => 'Upload a High-Quality Profile Photo',
                'next_step_desc' => 'A friendly, professional photo makes your profile instantly look credible, making customers significantly more likely to choose you over a generic icon.',
                'why_label' => '💡 Why It Matters',
                'why_text' => 'Trust is the ultimate currency. In a local marketplace, profiles with genuine, high-quality photos establish an immediate personal connection and feel more reliable.',
                'cta_label' => 'Add My Profile Photo',
                'cta_subtext' => 'Quickly upload from your phone or desktop',
                'quote' => '"Every great business starts with a great first impression."',
            ],

            'services' => [
                'label' => '🛠️ Services',
                'group' => 'provider',
                'subject' => '🛠️ Your Services = Your Visibility – Add Them Now',
                'badge' => 'Step 2 of 6',
                'headline' => 'Help Customers Find You Instantly 🛠️',
                'lead' => "Great progress, {{ provider_name }}! Your profile is looking sharper. Now, let's make sure you appear in search results when local customers look for the exact tasks they need.",
                'next_step_label' => 'Action Item',
                'next_step_title' => 'Specify the Services You Provide',
                'next_step_desc' => "Selecting all the specific services you offer ensures Speeda's search algorithm matches you with the right customers. Don't let potential jobs slip by!",
                'why_label' => '💡 Why It Matters',
                'why_text' => "If you don't list your services, you might remain invisible when customers search. Listing them unlocks direct match traffic and targets clients looking for your exact skillset.",
                'cta_label' => 'Add My Services',
                'cta_subtext' => 'Add your services in just 2 minutes',
                'quote' => '"The easier it is for customers to see what you offer, the easier it is for them to hire you."',
            ],

            'bio' => [
                'label' => '📝 Description',
                'group' => 'provider',
                'subject' => '📝 Your Story Is Your Greatest Sales Tool – Tell It',
                'badge' => 'Step 3 of 6',
                'headline' => 'Your Story Is Your Greatest Sales Tool 📝',
                'lead' => "Fantastic work, {{ provider_name }}! Your photo and services are set. Now, it's time to build a real connection with customers by sharing what makes your business unique and reliable.",
                'next_step_label' => 'Action Item',
                'next_step_title' => 'Write a Professional Business Description',
                'next_step_desc' => 'Introduce yourself, highlight your expertise, outline your standards of quality, and tell customers why choosing you is their best option.',
                'why_label' => '💡 Why It Matters',
                'why_text' => 'A clear and honest description answers common customer questions upfront, builds immediate confidence, and differentiates you from generic competitors.',
                'cta_label' => 'Write My Description',
                'cta_subtext' => 'A short paragraph of 3-4 sentences is perfect',
                'quote' => '"Your story is often the reason customers choose you over someone else."',
            ],

            'experience' => [
                'label' => '📅 Experience',
                'group' => 'provider',
                'subject' => '📅 Experience Wins Customers – Show Yours',
                'badge' => 'Step 4 of 6',
                'headline' => 'Experience Wins Customers – Show Yours 📅',
                'lead' => "You're building an incredible profile, {{ provider_name }}! Let's strengthen your authority and customer trust by highlighting your professional background.",
                'next_step_label' => 'Action Item',
                'next_step_title' => 'Add Your Years of Experience',
                'next_step_desc' => "Let customers know how long you've been working in your trade. Every year counts toward demonstrating your capability and dedication to quality.",
                'why_label' => '💡 Why It Matters',
                'why_text' => 'Experience is one of the top factors customers evaluate when comparing service providers. Explicitly stating your experience builds immediate trust and removes uncertainty.',
                'cta_label' => 'Add My Experience',
                'cta_subtext' => 'Takes only a single click to select',
                'quote' => '"Experience builds confidence—and confident customers are far more likely to hire."',
            ],

            'gallery' => [
                'label' => '🖼️ Gallery',
                'group' => 'provider',
                'subject' => "🖼️ Show, Don't Just Tell – Upload Your Best Work",
                'badge' => 'Step 5 of 6',
                'headline' => "Show, Don't Just Tell – Let Your Work Speak 🖼️",
                'lead' => "You're doing awesome, {{ provider_name }}! Your profile is almost complete. Now let's let your actual craftsmanship do the talking by showcasing visual proof of your best work.",
                'next_step_label' => 'Action Item',
                'next_step_title' => 'Add Photos to Your Work Gallery',
                'next_step_desc' => 'Upload clear before-and-after photos, pictures of your tools, completed projects, or your team on site. Let clients see the quality they are paying for!',
                'why_label' => '💡 Why It Matters',
                'why_text' => 'A picture is worth a thousand words. Profiles with a gallery see massive increases in user engagement. Clients love visualizing the quality of work they can expect.',
                'cta_label' => 'Upload My Photos',
                'cta_subtext' => "Add photos directly from your phone's camera roll",
                'quote' => '"Your work tells a story that words alone never can."',
            ],

            'service_areas' => [
                'label' => '🌍 Service Areas',
                'group' => 'provider',
                'subject' => '🌍 Last Step! Add Your Service Areas & Complete Your Profile',
                'badge' => 'Step 6 of 6 – Final Step!',
                'headline' => 'Expand Your Reach – More Areas = More Customers 🌍',
                'lead' => "You are at the finish line, {{ provider_name }}! Your profile is looking absolutely top-tier. Let's take the final step to define everywhere you are available to work, so we can connect you with as many local clients as possible.",
                'next_step_label' => 'Action Item',
                'next_step_title' => 'Specify Your Active Service Areas',
                'next_step_desc' => 'Select the cities, towns, or specific radius where you provide services. This ensures we show your business to the right search queries based on location.',
                'why_label' => '💡 Why It Matters',
                'why_text' => "If a customer searches for your services in a nearby suburb but you haven't explicitly added it to your areas, you won't show up. Multiply your lead potential by listing all regions you cover!",
                'cta_label' => 'Update My Service Areas',
                'cta_subtext' => 'Add all surrounding cities you serve in seconds',
                'quote' => '"The more places you serve, the more opportunities you create."',
            ],

            'complete' => [
                'label' => '🏆 Profile Complete',
                'group' => 'provider',
                'subject' => '🏆 Congratulations! Your Speeda Profile Is 100% Complete',
                'badge' => '',
                'headline' => "🏆 You're a Speeda All-Star, {{ provider_name }}!",
                'lead' => '✨ 🎉 Confetti time! You have successfully completed your profile. You are now officially ready to attract and impress customers as a fully verified, optimized service provider on Speeda.',
                'next_step_label' => '',
                'next_step_title' => '100% Completed Profile!',
                'next_step_desc' => 'Your profile has everything a customer looks for: a professional photo, detailed services, years of experience, a compelling bio, and service areas. Well done!',
                'why_label' => '',
                'why_text' => '',
                'cta_label' => 'View My Live Profile',
                'cta_subtext' => 'See what customers see when searching for you',
                'quote' => '',
            ],

            'reviews' => [
                'label' => '⭐ Reviews',
                'group' => 'provider',
                'subject' => '⭐ Build Your Reputation – Request Your First Review Today',
                'badge' => '',
                'headline' => "You've Built the Profile. Now Build the Trust. ⭐",
                'lead' => "Congrats again on your completed profile, {{ provider_name }}! Now it's time to trigger the single most powerful factor in a customer's hiring decision: authentic customer reviews.",
                'next_step_label' => 'Action Item',
                'next_step_title' => 'Ask Your Customers to Leave Their First Review',
                'next_step_desc' => 'Even one genuine 5-star review makes your profile stand out, boosts your search ranking, and gives future clients the peace of mind to click "Contact".',
                'why_label' => '💡 Why It Matters',
                'why_text' => 'Modern clients rely on word of mouth. By showcasing happy customer reviews, you let your history of great service do the selling for you.',
                'cta_label' => 'Request My First Review',
                'cta_subtext' => 'Share your review link directly with past clients',
                'quote' => '"Every great reputation starts with a single satisfied customer."',
            ],

            'client_welcome' => [
                'label' => '🎉 Client — Welcome',
                'group' => 'client',
                'subject' => '🎉 Welcome to Speeda! Find & Connect with Top Local Pros',
                'badge' => '🎉 Welcome to Speeda!',
                'headline' => 'Welcome aboard, {{ provider_name }}!',
                'lead' => "We are excited to have you join Speeda! Discover, compare, and connect with top-rated local service providers in your area in just a few clicks.",
                'next_step_label' => 'Get Started',
                'next_step_title' => 'Browse Verified Local Service Providers',
                'next_step_desc' => 'Need a plumber, electrician, painter, or contractor? Read real reviews from local customers and contact pros directly.',
                'why_label' => '💡 Why Speeda?',
                'why_text' => 'We verify local service providers and aggregate genuine customer reviews so you can hire with complete confidence every time.',
                'cta_label' => 'Explore Service Providers',
                'cta_subtext' => 'Find trusted local help in seconds',
                'quote' => '"Finding trusted local help has never been easier."',
            ],

            'client_first_review' => [
                'label' => '🌟 Client — First Review',
                'group' => 'client',
                'subject' => '🌟 Thank you for your first review on Speeda!',
                'badge' => '🌟 First Review Milestone!',
                'headline' => 'Thank you, {{ provider_name }}!',
                'lead' => 'You just shared your very first review on Speeda — and it means a lot! Honest reviews like yours help other people find trustworthy local service providers, and they help great local businesses get the recognition they deserve.',
                'next_step_label' => 'You\'re Making a Difference',
                'next_step_title' => 'Every review helps someone choose better',
                'next_step_desc' => 'Your experience is now visible to the community and guides others toward the right provider. Keep exploring and sharing — your voice matters here.',
                'why_label' => '💡 Community Impact',
                'why_text' => 'Local reviews drive transparency and build trust across the whole community.',
                'cta_label' => 'Discover More Providers',
                'cta_subtext' => 'Find and review the pros you work with',
                'quote' => '"The smallest act of sharing your experience can guide someone to exactly the help they were looking for."',
            ],

            'client_fifth_review' => [
                'label' => '🏆 Client — Fifth Review',
                'group' => 'client',
                'subject' => '🏆 5 reviews milestone — You\'re a Speeda Star!',
                'badge' => '🏆 5 Reviews Milestone!',
                'headline' => "You're a Speeda star, {{ provider_name }}!",
                'lead' => 'Five reviews — that\'s a real milestone! You\'ve become one of the trusted voices shaping our community. Thanks to contributors like you, choosing a local service provider on Speeda keeps getting easier and more reliable.',
                'next_step_label' => 'Top Contributor',
                'next_step_title' => 'Your reviews carry real weight',
                'next_step_desc' => 'People notice active, honest reviewers. Every review you add builds a stronger, more transparent community — and we\'re grateful to have you leading the way.',
                'why_label' => '💡 Super Contributor',
                'why_text' => 'Your consistent feedback makes the local marketplace safer and better for everyone.',
                'cta_label' => 'Keep Exploring Providers',
                'cta_subtext' => 'There\'s always another great pro to discover',
                'quote' => '"Great communities are built by people who show up and share — thank you for being one of them."',
            ],

            'provider_first_review_received' => [
                'label' => '🌟 Provider — First Review Received',
                'group' => 'provider',
                'subject' => '🌟 Congratulations! You received your first customer review!',
                'badge' => '🌟 First Review Received!',
                'headline' => 'You got your 1st customer review, {{ provider_name }}!',
                'lead' => 'Great news! A client just posted a verified review on your Speeda profile. Having customer reviews instantly boosts your credibility and helps new clients choose your business with confidence.',
                'next_step_label' => 'Milestone Reached',
                'next_step_title' => 'Your Reputation Is Growing',
                'next_step_desc' => 'Every positive review increases your visibility on Speeda. Check out your latest rating and keep up the fantastic service!',
                'why_label' => '💡 Why It Matters',
                'why_text' => 'Profiles with customer reviews get up to 3x more contact requests than unreviewed profiles.',
                'cta_label' => 'View My Profile & Reviews',
                'cta_subtext' => 'See your new customer review on Speeda',
                'quote' => '"Positive customer reviews build trust faster than any advertisement."',
            ],

            'provider_fifth_review_received' => [
                'label' => '🏆 Provider — 5 Reviews Received',
                'group' => 'provider',
                'subject' => '🏆 Amazing! You\'ve reached 5 customer reviews on Speeda!',
                'badge' => '🏆 5 Reviews Milestone!',
                'headline' => 'High 5, {{ provider_name }}! 5 Reviews Milestone!',
                'lead' => 'Congratulations on reaching 5 customer reviews! Service providers with 5 or more reviews gain top credibility and earn premium visibility on Speeda search results.',
                'next_step_label' => 'Top Rated Provider',
                'next_step_title' => 'Strong Community Reputation',
                'next_step_desc' => 'You\'re now established as a highly trusted local professional. Keep delivering great quality and asking satisfied clients to leave feedback!',
                'why_label' => '💡 Top Tier Status',
                'why_text' => 'Reaching 5 reviews puts your profile in the top tier of customer trust.',
                'cta_label' => 'View My Profile',
                'cta_subtext' => 'Showcase your top-rated business profile',
                'quote' => '"Quality service speaks for itself — 5 times over!"',
            ],
        ];
    }

    /**
     * Keys with their human label + group, for the admin list.
     *
     * @return array<string, array{label:string, group:string}>
     */
    public static function catalogue(): array
    {
        return collect(self::defaults())
            ->map(fn ($d) => ['label' => $d['label'], 'group' => $d['group']])
            ->all();
    }

    public static function isKnownKey(string $key): bool
    {
        return array_key_exists($key, self::defaults());
    }
}
