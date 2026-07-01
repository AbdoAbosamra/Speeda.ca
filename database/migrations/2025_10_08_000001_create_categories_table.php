<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

return new class extends Migration
{
    private const CITIES = ['Laval', 'Montreal', 'Ottawa', 'Gatineau'];

    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug'); // لا unique هنا
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#6c757d');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->boolean('is_section')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // الـ slug فريد داخل القسم فقط
            $table->unique(['slug', 'parent_id'], 'categories_slug_parent_unique');
        });

        // Skip reference-data seeding under tests; the suite manages its own categories.
        if (app()->runningUnitTests()) {
            return;
        }

        // === الأقسام الرئيسية ===
        $sections = [
            ['name' => 'Automotive Services',       'icon' => 'fas fa-car',      'color' => '#dc3545', 'sort' => 1],
            ['name' => 'Home & Property Services',  'icon' => 'fas fa-home',     'color' => '#28a745', 'sort' => 2],
            ['name' => 'Professional & Business Services', 'icon' => 'fas fa-briefcase', 'color' => '#007bff', 'sort' => 3],
            ['name' => 'Personal & Lifestyle Services', 'icon' => 'fas fa-heart', 'color' => '#fd7e14', 'sort' => 4],
            ['name' => 'Technical & Repair Services', 'icon' => 'fas fa-tools', 'color' => '#6f42c1', 'sort' => 5],
            ['name' => 'Event & Entertainment Services', 'icon' => 'fas fa-glass-cheers', 'color' => '#e83e8c', 'sort' => 6],
        ];

        $sectionIds = [];
        foreach ($sections as $sec) {
            $slug = Str::slug($sec['name']);
            $id = DB::table('categories')->insertGetId([
                'name' => $sec['name'],
                'slug' => $slug,
                'icon' => $sec['icon'],
                'color' => $sec['color'],
                'is_active' => true,
                'sort_order' => $sec['sort'],
                'is_section' => true,
                'parent_id' => null, // مهم: null للأقسام الرئيسية
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sectionIds[$sec['name']] = $id;
        }

        // === الفئات الفرعية ===
        $subcategories = $this->getSubcategories();
        $insert = [];

        foreach ($subcategories as $sectionName => $items) {
            $parentId = $sectionIds[$sectionName] ?? null;
            if (!$parentId) continue;

            foreach ($items as $item) {
                $insert[] = [
                    'name' => $item['name'],
                    'slug' => $this->uniqueSlug($item['name'], $parentId),
                    'description' => $this->desc($item['name']),
                    'icon' => $item['icon'],
                    'color' => $sections[array_search($sectionName, array_column($sections, 'name'))]['color'],
                    'is_active' => true,
                    'sort_order' => $item['sort'],
                    'parent_id' => $parentId,
                    'is_section' => false,
                    'meta_title' => $item['name'] . ' | Professional Services',
                    'meta_description' => $this->desc($item['name']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // إدخال بالدفعات
        foreach (array_chunk($insert, 20) as $chunk) {
            DB::table('categories')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }

    private function getSubcategories(): array
    {
        return [
            'Automotive Services' => [
                ['name' => 'Car Mechanics', 'icon' => 'fas fa-tools', 'sort' => 1],
                ['name' => 'Oil Change Services', 'icon' => 'fas fa-oil-can', 'sort' => 2],
                ['name' => 'Electric & Hybrid Car Services', 'icon' => 'fas fa-car-battery', 'sort' => 3],
                ['name' => 'Tire Change & Repair', 'icon' => 'fas fa-tire', 'sort' => 4],
                ['name' => 'Car Dealers', 'icon' => 'fas fa-car-side', 'sort' => 5],
                ['name' => 'Cars Inspections (Safety) for Uber', 'icon' => 'fas fa-clipboard-check', 'sort' => 6],
                ['name' => 'Auto Body Repair', 'icon' => 'fas fa-hammer', 'sort' => 7],
                ['name' => 'Car Wash & Detailing', 'icon' => 'fas fa-soap', 'sort' => 8],
                ['name' => 'Others', 'icon' => 'fas fa-car', 'sort' => 9],
            ],
            'Home & Property Services' => [
                ['name' => 'Roofing Contractors', 'icon' => 'fas fa-house-damage', 'sort' => 1],
                ['name' => 'Carpentry Services', 'icon' => 'fas fa-hammer', 'sort' => 2],
                ['name' => 'Painting Services', 'icon' => 'fas fa-paint-roller', 'sort' => 3],
                ['name' => 'Plumbing Services', 'icon' => 'fas fa-faucet', 'sort' => 4],
                ['name' => 'Electrical Technicians', 'icon' => 'fas fa-bolt', 'sort' => 5],
                ['name' => 'Handyman Services', 'icon' => 'fas fa-toolbox', 'sort' => 6],
                ['name' => 'Moving Services', 'icon' => 'fas fa-truck-moving', 'sort' => 7],
                ['name' => 'Cleaning Services', 'icon' => 'fas fa-broom', 'sort' => 8],
                ['name' => 'Landscaping & Gardening', 'icon' => 'fas fa-leaf', 'sort' => 9],
                ['name' => 'Home Renovation', 'icon' => 'fas fa-paint-brush', 'sort' => 10],
                ['name' => 'Pest Control', 'icon' => 'fas fa-bug', 'sort' => 11],
                ['name' => 'Security System Installation', 'icon' => 'fas fa-shield-alt', 'sort' => 12],
                ['name' => 'Snow Removal', 'icon' => 'fas fa-snowflake', 'sort' => 13],
                ['name' => 'HVAC Services', 'icon' => 'fas fa-fan', 'sort' => 14],
                ['name' => 'Others', 'icon' => 'fas fa-home', 'sort' => 15],
            ],
            'Professional & Business Services' => [
                ['name' => 'Accounting & Bookkeeping', 'icon' => 'fas fa-calculator', 'sort' => 1],
                ['name' => 'Insurance Brokers', 'icon' => 'fas fa-user-tie', 'sort' => 2],
                ['name' => 'Lawyers & Legal Advisors', 'icon' => 'fas fa-gavel', 'sort' => 3],
                ['name' => 'Translators & Interpreters', 'icon' => 'fas fa-language', 'sort' => 4],
                ['name' => 'Real Estate Agents', 'icon' => 'fas fa-sign', 'sort' => 5],
                ['name' => 'Marketing & Advertising', 'icon' => 'fas fa-bullhorn', 'sort' => 6],
                ['name' => 'Others', 'icon' => 'fas fa-briefcase', 'sort' => 7],
            ],
            'Personal & Lifestyle Services' => [
                ['name' => 'Beauty & Personal Care', 'icon' => 'fas fa-spa', 'sort' => 1],
                ['name' => 'Restaurants & Catering', 'icon' => 'fas fa-utensils', 'sort' => 2],
                ['name' => 'Dental & Oral Care', 'icon' => 'fas fa-tooth', 'sort' => 3],
                ['name' => 'Fitness Trainers', 'icon' => 'fas fa-dumbbell', 'sort' => 4],
                ['name' => 'Massage Therapy', 'icon' => 'fas fa-hands', 'sort' => 5],
                ['name' => 'Hair Stylists', 'icon' => 'fas fa-cut', 'sort' => 6],
                ['name' => 'Makeup Artists', 'icon' => 'fas fa-palette', 'sort' => 7],
                ['name' => 'Event Planners', 'icon' => 'fas fa-calendar-alt', 'sort' => 8],
                ['name' => 'Barber', 'icon' => 'fas fa-scissors', 'sort' => 9],
                ['name' => 'Others', 'icon' => 'fas fa-spa', 'sort' => 10],
            ],
            'Technical & Repair Services' => [
                ['name' => 'Appliance Repair', 'icon' => 'fas fa-blender', 'sort' => 1],
                ['name' => 'Computer Repair', 'icon' => 'fas fa-desktop', 'sort' => 2],
                ['name' => 'Phone Repair', 'icon' => 'fas fa-mobile-alt', 'sort' => 3],
                ['name' => 'AC & Refrigeration', 'icon' => 'fas fa-snowflake', 'sort' => 4],
                ['name' => 'Generator Repair', 'icon' => 'fas fa-bolt', 'sort' => 5],
                ['name' => 'Others', 'icon' => 'fas fa-cogs', 'sort' => 6],
            ],
            'Event & Entertainment Services' => [
                ['name' => 'Photographers', 'icon' => 'fas fa-camera', 'sort' => 1],
                ['name' => 'Videographers', 'icon' => 'fas fa-video', 'sort' => 2],
                ['name' => 'DJs & Music', 'icon' => 'fas fa-music', 'sort' => 3],
                ['name' => 'Catering Services', 'icon' => 'fas fa-utensils', 'sort' => 4],
                ['name' => 'Decorators', 'icon' => 'fas fa-palette', 'sort' => 5],
                ['name' => 'Event Planners', 'icon' => 'fas fa-calendar-alt', 'sort' => 6],
                ['name' => 'Entertainers', 'icon' => 'fas fa-theater-masks', 'sort' => 7],
                ['name' => 'Others', 'icon' => 'fas fa-glass-cheers', 'sort' => 8],
            ],
        ];
    }

    private function uniqueSlug(string $name, ?int $parentId): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (DB::table('categories')
            ->where('slug', $slug)
            ->where('parent_id', $parentId)
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function desc(string $name): string
    {
        return "Professional {$name} services in " . implode(', ', self::CITIES) . ".";
    }
};
