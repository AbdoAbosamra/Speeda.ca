<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();

            // === العلاقات الأساسية ===
            $table->foreignId('user_id')
                  ->unique() // ← مهم جدًا
                  ->constrained('users')
                  ->onDelete('cascade');

        // Allow nullable category/location so a ServiceProvider record can be created
        // even if the category/location mapping isn't found at registration time.
        $table->foreignId('category_id')
            ->nullable()
            ->constrained('categories')
            ->onDelete('cascade');

        $table->foreignId('location_id')
            ->nullable()
            ->constrained('locations')
            ->onDelete('cascade');

            // === التصديق (تصحيح الإملاء) ===
            $table->boolean('is_certified')->default(false); // ← تصحيح
            $table->string('certification')->nullable();     // ← تصحيح

            // === المعلومات المهنية ===
            $table->text('bio')->nullable();
            $table->text('services_offered')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->integer('experience_years')->nullable();

            // === معلومات الاتصال ===
            $table->string('phone')->unique()->nullable(); // ← unique
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();

            // === التوفر والموقع ===
            $table->string('service_area')->nullable();
            $table->boolean('available_weekends')->default(false);
            $table->boolean('available_evenings')->default(false);
            $table->json('availability_schedule')->nullable();

            // === التفاصيل المهنية ===
            $table->json('languages')->nullable();
            $table->json('specializations')->nullable();

            // === الوسائط ===
            $table->string('profile_image')->nullable();
            $table->json('portfolio_images')->nullable();
            $table->json('portfolio_videos')->nullable();

            // === معلومات الأعمال ===
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->enum('business_type', ['individual', 'company'])->default('individual');
            $table->string('company_name')->nullable();
            $table->string('business_license')->nullable();

            // === التقييمات ===
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            $table->integer('completed_jobs')->default(0);

            // === الخدمات الطارئة ===
            $table->boolean('emergency_available')->default(false);
            $table->integer('response_time_hours')->nullable();

            // === الوقت ===
            $table->timestamps();
            $table->softDeletes();

            // === حذف profession (مكرر مع category_id) ===
            // $table->string('profession')->nullable(); // ← احذف
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};
