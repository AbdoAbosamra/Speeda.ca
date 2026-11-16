<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            // Nullable for existing/admin offers. One key per provider form submission.
            $table->char('submission_key', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropUnique(['submission_key']);
            $table->dropColumn('submission_key');
        });
    }
};
