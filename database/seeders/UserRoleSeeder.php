<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        DB::connection()->disableQueryLog();

        $roles = [
            ['name' => 'Customer', 'slug' => 'customer', 'description' => 'Default customer role', 'is_active' => true, 'is_default' => true],
            ['name' => 'Service Provider', 'slug' => 'service-provider', 'description' => 'Service provider role', 'is_active' => true, 'is_default' => false],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrator role', 'is_active' => true, 'is_default' => false],
        ];

        $roleData = [];
        foreach ($roles as $role) {
            if (!UserRole::where('slug', $role['slug'])->exists()) {
                $roleData[] = [
                    'name' => $role['name'],
                    'slug' => $role['slug'],
                    'description' => $role['description'],
                    'is_active' => $role['is_active'],
                    'is_default' => $role['is_default'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($roleData)) {
            UserRole::insert($roleData);
        }

        DB::connection()->enableQueryLog();

        $this->command->info('User roles seeded successfully! Memory usage: ' . (memory_get_usage() / 1024 / 1024) . ' MB');
    }
}
