<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CITIES = ['Laval', 'Montreal', 'Ottawa', 'Gatineau'];

    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            if (DB::getDriverName() === 'sqlite') {
                $table->string('city')->unique();
            } else {
                $table->enum('city', self::CITIES)->unique();
            }
            $table->timestamps();
        });

        // Skip reference-data seeding under tests; the suite manages its own locations.
        if (app()->runningUnitTests()) {
            return;
        }

        // إدخال الـ 4 مدن تلقائيًا (idempotent)
        foreach (self::CITIES as $city) {
            DB::table('locations')->updateOrInsert(
                ['city' => $city],
                ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
