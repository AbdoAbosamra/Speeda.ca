<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        // Update existing category name
        DB::table('categories')
            ->where('id', 10)
            ->update([
                'name' => 'Tire Balancing & Wheel Alignment',
                'slug' => 'tire-balancing-wheel-alignment',
                'updated_at' => now(),
            ]);

        DB::table('categories')
            ->where('id', 31)
            ->update([
                'name' => 'Accounting & Bookkeeping + Tax Preparation',
                'slug' => 'accounting-bookkeeping-tax-preparation',
                'updated_at' => now(),
            ]);

        // Remove Appliance Repair from Technical section (ID 48)
        DB::table('categories')->where('id', 48)->delete();

        // Insert new Automotive Services (Section 1)
        $newAutomotiveServices = [
            ['id' => 64, 'name' => 'Towing Services', 'slug' => 'towing-services', 'parent_id' => 1, 'icon' => 'fas fa-truck-pickup', 'color' => '#dc3545', 'sort_order' => 9],
            ['id' => 65, 'name' => 'Lockout Service', 'slug' => 'lockout-service', 'parent_id' => 1, 'icon' => 'fas fa-key', 'color' => '#dc3545', 'sort_order' => 10],
            ['id' => 66, 'name' => 'Winching / Vehicle Recovery', 'slug' => 'winching-vehicle-recovery', 'parent_id' => 1, 'icon' => 'fas fa-anchor', 'color' => '#dc3545', 'sort_order' => 11],
            ['id' => 67, 'name' => 'Jump Start (Battery Boost)', 'slug' => 'jump-start-battery-boost', 'parent_id' => 1, 'icon' => 'fas fa-car-battery', 'color' => '#dc3545', 'sort_order' => 12],
            ['id' => 68, 'name' => 'Roadside Assistance (24/7)', 'slug' => 'roadside-assistance-24-7', 'parent_id' => 1, 'icon' => 'fas fa-ambulance', 'color' => '#dc3545', 'sort_order' => 13],
        ];

        foreach ($newAutomotiveServices as $service) {
            DB::table('categories')->insert(array_merge($service, [
                'is_section' => 0,
                'is_active' => 1,
                'description' => 'Professional ' . $service['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'meta_title' => $service['name'] . ' | Professional Services',
                'meta_description' => 'Professional ' . $service['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Insert new Home & Property Services (Section 2)
        $newHomeServices = [
            ['id' => 69, 'name' => 'Appliance Repair', 'slug' => 'appliance-repair-home', 'parent_id' => 2, 'icon' => 'fas fa-blender', 'color' => '#28a745', 'sort_order' => 15],
            ['id' => 70, 'name' => 'Flooring Installation & Repair', 'slug' => 'flooring-installation-repair', 'parent_id' => 2, 'icon' => 'fas fa-layer-group', 'color' => '#28a745', 'sort_order' => 16],
            ['id' => 71, 'name' => 'Window & Door Installation / Repair', 'slug' => 'window-door-installation-repair', 'parent_id' => 2, 'icon' => 'fas fa-door-open', 'color' => '#28a745', 'sort_order' => 17],
            ['id' => 72, 'name' => 'Gutter Cleaning & Installation', 'slug' => 'gutter-cleaning-installation', 'parent_id' => 2, 'icon' => 'fas fa-water', 'color' => '#28a745', 'sort_order' => 18],
            ['id' => 73, 'name' => 'Fencing Installation & Repair', 'slug' => 'fencing-installation-repair', 'parent_id' => 2, 'icon' => 'fas fa-border-style', 'color' => '#28a745', 'sort_order' => 19],
            ['id' => 74, 'name' => 'Junk Removal', 'slug' => 'junk-removal', 'parent_id' => 2, 'icon' => 'fas fa-trash', 'color' => '#28a745', 'sort_order' => 20],
            ['id' => 75, 'name' => 'Water Damage Restoration', 'slug' => 'water-damage-restoration', 'parent_id' => 2, 'icon' => 'fas fa-tint', 'color' => '#28a745', 'sort_order' => 21],
            ['id' => 76, 'name' => 'Garage Door Installation & Repair', 'slug' => 'garage-door-installation-repair', 'parent_id' => 2, 'icon' => 'fas fa-garage', 'color' => '#28a745', 'sort_order' => 22],
            ['id' => 77, 'name' => 'General Contractor', 'slug' => 'general-contractor', 'parent_id' => 2, 'icon' => 'fas fa-hard-hat', 'color' => '#28a745', 'sort_order' => 23],
        ];

        foreach ($newHomeServices as $service) {
            DB::table('categories')->insert(array_merge($service, [
                'is_section' => 0,
                'is_active' => 1,
                'description' => 'Professional ' . $service['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'meta_title' => $service['name'] . ' | Professional Services',
                'meta_description' => 'Professional ' . $service['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Insert new Professional & Business Services (Section 3)
        $newProfessionalServices = [
            ['id' => 78, 'name' => 'HR & Recruiting', 'slug' => 'hr-recruiting', 'parent_id' => 3, 'icon' => 'fas fa-users-cog', 'color' => '#007bff', 'sort_order' => 7],
            ['id' => 79, 'name' => 'IT Support', 'slug' => 'it-support', 'parent_id' => 3, 'icon' => 'fas fa-server', 'color' => '#007bff', 'sort_order' => 8],
            ['id' => 80, 'name' => 'Web Design', 'slug' => 'web-design', 'parent_id' => 3, 'icon' => 'fas fa-globe', 'color' => '#007bff', 'sort_order' => 9],
            ['id' => 81, 'name' => 'Graphic Design', 'slug' => 'graphic-design', 'parent_id' => 3, 'icon' => 'fas fa-pen-nib', 'color' => '#007bff', 'sort_order' => 10],
            ['id' => 82, 'name' => 'Notary Public', 'slug' => 'notary-public', 'parent_id' => 3, 'icon' => 'fas fa-stamp', 'color' => '#007bff', 'sort_order' => 11],
            ['id' => 83, 'name' => 'Printing Services', 'slug' => 'printing-services', 'parent_id' => 3, 'icon' => 'fas fa-print', 'color' => '#007bff', 'sort_order' => 12],
        ];

        foreach ($newProfessionalServices as $service) {
            DB::table('categories')->insert(array_merge($service, [
                'is_section' => 0,
                'is_active' => 1,
                'description' => 'Professional ' . $service['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'meta_title' => $service['name'] . ' | Professional Services',
                'meta_description' => 'Professional ' . $service['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Insert new Personal & Lifestyle Services (Section 4)
        $newPersonalServices = [
            ['id' => 84, 'name' => 'Tattoo & Piercing Artists', 'slug' => 'tattoo-piercing-artists', 'parent_id' => 4, 'icon' => 'fas fa-paint-brush', 'color' => '#fd7e14', 'sort_order' => 10],
            ['id' => 85, 'name' => 'Pet Grooming', 'slug' => 'pet-grooming', 'parent_id' => 4, 'icon' => 'fas fa-paw', 'color' => '#fd7e14', 'sort_order' => 11],
            ['id' => 86, 'name' => 'Childcare / Babysitting', 'slug' => 'childcare-babysitting', 'parent_id' => 4, 'icon' => 'fas fa-baby', 'color' => '#fd7e14', 'sort_order' => 12],
        ];

        foreach ($newPersonalServices as $service) {
            DB::table('categories')->insert(array_merge($service, [
                'is_section' => 0,
                'is_active' => 1,
                'description' => 'Professional ' . $service['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'meta_title' => $service['name'] . ' | Professional Services',
                'meta_description' => 'Professional ' . $service['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Update sort orders for Technical & Repair Services
        DB::table('categories')->where('id', 49)->update(['sort_order' => 1]);
        DB::table('categories')->where('id', 50)->update(['sort_order' => 2]);
        DB::table('categories')->where('id', 51)->update(['sort_order' => 3]);
        DB::table('categories')->where('id', 52)->update(['sort_order' => 4]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original category names
        DB::table('categories')
            ->where('id', 10)
            ->update([
                'name' => 'Tire Change & Repair',
                'slug' => 'tire-change-repair',
            ]);

        DB::table('categories')
            ->where('id', 31)
            ->update([
                'name' => 'Accounting & Bookkeeping',
                'slug' => 'accounting-bookkeeping',
            ]);

        // Restore Appliance Repair to Technical section
        DB::table('categories')->insert([
            'id' => 48,
            'name' => 'Appliance Repair',
            'slug' => 'appliance-repair',
            'parent_id' => 5,
            'icon' => 'fas fa-blender',
            'color' => '#6f42c1',
            'sort_order' => 1,
            'is_section' => 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Delete new categories
        DB::table('categories')->whereIn('id', [64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86])->delete();
    }
};
