<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    private const ARABIC_TITLES = [
        'دليل اختيار أفضل مزودي الخدمات المنزلية في كندا',
        'كيف تحصل على عروض أسعار مجانية من المحترفين',
        'نصائح للتحقق من مصداقية مزودي الخدمة قبل التعاقد',
        'أفضل ممارسات صيانة المنزل لفصل الشتاء الكندي',
        'دليلك الكامل لاختيار السباك المناسب',
        'كيف توفر المال على فواتير الكهرباء في المنزل',
        'نصائح لاختيار مقاول تجديد المنازل',
        'أفضل 10 خدمات منزلية يجب أن تعرفها',
    ];

    private const ENGLISH_TITLES = [
        'Complete Guide to Finding Local Service Providers',
        'How to Get Free Quotes from Professionals',
        'Tips for Verifying Service Provider Credentials',
        'Essential Home Maintenance for Canadian Winter',
        'The Ultimate Guide to Choosing a Plumber',
        'Save Money on Home Energy Bills',
        'How to Hire the Right Renovation Contractor',
        'Top 10 Home Services You Need to Know',
    ];

    public function definition(): array
    {
        $enTitle = fake()->randomElement(self::ENGLISH_TITLES);
        $arTitle = fake()->randomElement(self::ARABIC_TITLES);
        $slug = Str::slug($enTitle) . '-' . fake()->unique()->numberBetween(1, 999);

        return [
            'author_id' => User::factory()->admin(),
            'category_id' => \App\Models\Category::factory(),
            'title' => $enTitle,
            'title_ar' => $arTitle,
            'title_en' => $enTitle,
            'title_fr' => $enTitle,
            'slug' => $slug,
            'content' => fake()->paragraphs(5, true),
            'content_ar' => fake()->paragraphs(3, true),
            'content_en' => fake()->paragraphs(5, true),
            'content_fr' => fake()->paragraphs(4, true),
            'excerpt' => fake()->paragraph(2),
            'excerpt_ar' => fake()->paragraph(1),
            'excerpt_en' => fake()->paragraph(2),
            'excerpt_fr' => fake()->paragraph(2),
            'status' => 'draft',
            'is_published' => false,
            'is_featured' => false,
            'allow_indexing' => true,
            'reading_time_minutes' => fake()->numberBetween(3, 15),
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn() => [
            'status' => 'published',
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-12 months', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn() => [
            'status' => 'draft',
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn() => ['is_featured' => true]);
    }

    public function arabic(): static
    {
        $title = fake()->randomElement(self::ARABIC_TITLES);
        return $this->state(fn() => [
            'title' => $title,
            'title_ar' => $title,
            'content_ar' => fake()->paragraphs(4, true),
        ]);
    }

    public function authoredBy(int $authorId): static
    {
        return $this->state(fn() => ['author_id' => $authorId]);
    }
}
