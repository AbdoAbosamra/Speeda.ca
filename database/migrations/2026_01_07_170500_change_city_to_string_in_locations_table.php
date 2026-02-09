<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert `city` enum column to string to allow adding new cities from admin panel.
     */
    public function up(): void
    {
        // Use raw statement to change column type safely across MySQL versions
        // Keep existing values intact.
        DB::statement("ALTER TABLE `locations` MODIFY `city` VARCHAR(255) NOT NULL;");
        // Keep unique index on city
        DB::statement("ALTER TABLE `locations` ADD UNIQUE (`city`);");
    }

    /**
     * Reverse the migrations.
     * Convert city back to enum with original default set (best-effort).
     */
    public function down(): void
    {
        // Drop unique constraint if exists (name may vary across DB engines)
        // Attempt by column alteration to enum
        DB::statement("ALTER TABLE `locations` MODIFY `city` ENUM('Laval','Montreal','Ottawa','Gatineau') NOT NULL;");
    }
};
