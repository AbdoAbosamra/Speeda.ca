<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * SAFE: This migration only adds 'admin' to the enum without affecting existing data
     */
    public function up(): void
    {
        // For MySQL, we need to modify the enum column
        // This is safe and won't affect existing data
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('client', 'service_provider', 'admin') DEFAULT 'client'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Before removing 'admin', ensure no admin users exist
        $adminCount = DB::table('users')->where('role', 'admin')->count();
        
        if ($adminCount > 0) {
            throw new \Exception('Cannot remove admin role: ' . $adminCount . ' admin user(s) exist. Please change their roles first.');
        }
        
        // Revert to original enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('client', 'service_provider') DEFAULT 'client'");
    }
};
