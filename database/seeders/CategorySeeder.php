<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories (cross-database safe)
        Schema::disableForeignKeyConstraints();
        DB::table('categories')->truncate();
        Schema::enableForeignKeyConstraints();

        $timestamp = '2025-11-25 16:17:30';

        // Insert Sections (7 sections)
        $sections = [
            ['id' => 1, 'name' => 'Automotive Services', 'slug' => 'automotive-services', 'icon' => 'fas fa-car', 'color' => '#dc3545', 'sort_order' => 1],
            ['id' => 2, 'name' => 'Home & Property Services', 'slug' => 'home-property-services', 'icon' => 'fas fa-home', 'color' => '#28a745', 'sort_order' => 2],
            ['id' => 3, 'name' => 'Professional & Business Services', 'slug' => 'professional-business-services', 'icon' => 'fas fa-briefcase', 'color' => '#007bff', 'sort_order' => 3],
            ['id' => 4, 'name' => 'Personal & Lifestyle Services', 'slug' => 'personal-lifestyle-services', 'icon' => 'fas fa-heart', 'color' => '#fd7e14', 'sort_order' => 4],
            ['id' => 5, 'name' => 'Technical & Repair Services', 'slug' => 'technical-repair-services', 'icon' => 'fas fa-tools', 'color' => '#6f42c1', 'sort_order' => 5],
            ['id' => 6, 'name' => 'Event & Entertainment Services', 'slug' => 'event-entertainment-services', 'icon' => 'fas fa-glass-cheers', 'color' => '#e83e8c', 'sort_order' => 6],
            ['id' => 62, 'name' => 'Others', 'slug' => 'others-1', 'icon' => null, 'color' => '#6c757d', 'sort_order' => 7, 'description' => 'Other services'],
        ];

        foreach ($sections as $section) {
            DB::table('categories')->insert(array_merge($section, [
                'is_section' => 1,
                'is_active' => 1,
                'parent_id' => null,
                'created_at' => $section['id'] == 62 ? '2025-12-03 12:43:56' : $timestamp,
                'updated_at' => $section['id'] == 62 ? '2025-12-03 12:43:56' : $timestamp,
            ]));
        }

        // Insert Categories (68 categories - expanded from 50)
        $categories = [
            // Automotive Services (Section 1) - 11 services (removed Towing, Winching, Jump Start)
            ['id' => 7, 'name' => 'Car Mechanics', 'slug' => 'car-mechanics', 'parent_id' => 1, 'icon' => 'fas fa-tools', 'color' => '#dc3545', 'sort_order' => 1],
            ['id' => 8, 'name' => 'Oil Change Services', 'slug' => 'oil-change-services', 'parent_id' => 1, 'icon' => 'fas fa-oil-can', 'color' => '#dc3545', 'sort_order' => 2],
            ['id' => 9, 'name' => 'Electric & Hybrid Car Services', 'slug' => 'electric-hybrid-car-services', 'parent_id' => 1, 'icon' => 'fas fa-car-battery', 'color' => '#dc3545', 'sort_order' => 3],
            ['id' => 10, 'name' => 'Tire Balancing & Wheel Alignment', 'slug' => 'tire-balancing-wheel-alignment', 'parent_id' => 1, 'icon' => 'fas fa-tire', 'color' => '#dc3545', 'sort_order' => 4],
            ['id' => 11, 'name' => 'Car Dealers', 'slug' => 'car-dealers', 'parent_id' => 1, 'icon' => 'fas fa-car-side', 'color' => '#dc3545', 'sort_order' => 5],
            ['id' => 12, 'name' => 'Cars Inspections (Safety) for Uber', 'slug' => 'cars-inspections-safety-for-uber', 'parent_id' => 1, 'icon' => 'fas fa-clipboard-check', 'color' => '#dc3545', 'sort_order' => 6],
            ['id' => 13, 'name' => 'Auto Body Repair', 'slug' => 'auto-body-repair', 'parent_id' => 1, 'icon' => 'fas fa-hammer', 'color' => '#dc3545', 'sort_order' => 7],
            ['id' => 14, 'name' => 'Car Wash & Detailing', 'slug' => 'car-wash-detailing', 'parent_id' => 1, 'icon' => 'fas fa-soap', 'color' => '#dc3545', 'sort_order' => 8],
            ['id' => 65, 'name' => 'Lockout Service', 'slug' => 'lockout-service', 'parent_id' => 1, 'icon' => 'fas fa-key', 'color' => '#dc3545', 'sort_order' => 9],
            ['id' => 68, 'name' => 'Roadside Assistance (24/7)', 'slug' => 'roadside-assistance-24-7', 'parent_id' => 1, 'icon' => 'fas fa-ambulance', 'color' => '#dc3545', 'sort_order' => 10],

            // Home & Property Services (Section 2) - 22 services (removed Window & Door, replaced with Repairs And Maintenance)
            ['id' => 16, 'name' => 'Roofing Contractors', 'slug' => 'roofing-contractors', 'parent_id' => 2, 'icon' => 'fas fa-house-damage', 'color' => '#28a745', 'sort_order' => 1],
            ['id' => 17, 'name' => 'Carpentry Services', 'slug' => 'carpentry-services', 'parent_id' => 2, 'icon' => 'fas fa-hammer', 'color' => '#28a745', 'sort_order' => 2],
            ['id' => 18, 'name' => 'Painting Services', 'slug' => 'painting-services', 'parent_id' => 2, 'icon' => 'fas fa-paint-roller', 'color' => '#28a745', 'sort_order' => 3],
            ['id' => 19, 'name' => 'Plumbing Services', 'slug' => 'plumbing-services', 'parent_id' => 2, 'icon' => 'fas fa-faucet', 'color' => '#28a745', 'sort_order' => 4],
            ['id' => 20, 'name' => 'Electrical Technicians', 'slug' => 'electrical-technicians', 'parent_id' => 2, 'icon' => 'fas fa-bolt', 'color' => '#28a745', 'sort_order' => 5],
            ['id' => 21, 'name' => 'Handyman Services', 'slug' => 'handyman-services', 'parent_id' => 2, 'icon' => 'fas fa-toolbox', 'color' => '#28a745', 'sort_order' => 6],
            ['id' => 22, 'name' => 'Moving Services', 'slug' => 'moving-services', 'parent_id' => 2, 'icon' => 'fas fa-truck-moving', 'color' => '#28a745', 'sort_order' => 7],
            ['id' => 23, 'name' => 'Cleaning Services', 'slug' => 'cleaning-services', 'parent_id' => 2, 'icon' => 'fas fa-broom', 'color' => '#28a745', 'sort_order' => 8],
            ['id' => 24, 'name' => 'Landscaping & Gardening', 'slug' => 'landscaping-gardening', 'parent_id' => 2, 'icon' => 'fas fa-leaf', 'color' => '#28a745', 'sort_order' => 9],
            ['id' => 25, 'name' => 'Home Renovation', 'slug' => 'home-renovation', 'parent_id' => 2, 'icon' => 'fas fa-paint-brush', 'color' => '#28a745', 'sort_order' => 10],
            ['id' => 26, 'name' => 'Pest Control', 'slug' => 'pest-control', 'parent_id' => 2, 'icon' => 'fas fa-bug', 'color' => '#28a745', 'sort_order' => 11],
            ['id' => 27, 'name' => 'Security System Installation', 'slug' => 'security-system-installation', 'parent_id' => 2, 'icon' => 'fas fa-shield-alt', 'color' => '#28a745', 'sort_order' => 12],
            ['id' => 28, 'name' => 'Snow Removal', 'slug' => 'snow-removal', 'parent_id' => 2, 'icon' => 'fas fa-snowflake', 'color' => '#28a745', 'sort_order' => 13],
            ['id' => 29, 'name' => 'HVAC Services', 'slug' => 'hvac-services', 'parent_id' => 2, 'icon' => 'fas fa-fan', 'color' => '#28a745', 'sort_order' => 14],
            ['id' => 69, 'name' => 'Appliance Repair', 'slug' => 'appliance-repair-home', 'parent_id' => 2, 'icon' => 'fas fa-blender', 'color' => '#28a745', 'sort_order' => 15],
            ['id' => 70, 'name' => 'Flooring Installation & Repair', 'slug' => 'flooring-installation-repair', 'parent_id' => 2, 'icon' => 'fas fa-layer-group', 'color' => '#28a745', 'sort_order' => 16],
            ['id' => 71, 'name' => 'Repairs And Maintenance', 'slug' => 'repairs-and-maintenance', 'parent_id' => 2, 'icon' => 'fas fa-tools', 'color' => '#28a745', 'sort_order' => 17],
            ['id' => 73, 'name' => 'Fencing Installation & Repair', 'slug' => 'fencing-installation-repair', 'parent_id' => 2, 'icon' => 'fas fa-border-style', 'color' => '#28a745', 'sort_order' => 18],
            ['id' => 74, 'name' => 'Junk Removal', 'slug' => 'junk-removal', 'parent_id' => 2, 'icon' => 'fas fa-trash', 'color' => '#28a745', 'sort_order' => 19],
            ['id' => 75, 'name' => 'Water Damage Restoration', 'slug' => 'water-damage-restoration', 'parent_id' => 2, 'icon' => 'fas fa-tint', 'color' => '#28a745', 'sort_order' => 20],
            ['id' => 76, 'name' => 'Garage Door Installation & Repair', 'slug' => 'garage-door-installation-repair', 'parent_id' => 2, 'icon' => 'fas fa-garage', 'color' => '#28a745', 'sort_order' => 21],

            // Professional & Business Services (Section 3) - 13 services (6 original + 7 new)
            ['id' => 31, 'name' => 'Accounting & Bookkeeping + Tax Preparation', 'slug' => 'accounting-bookkeeping-tax-preparation', 'parent_id' => 3, 'icon' => 'fas fa-calculator', 'color' => '#007bff', 'sort_order' => 1],
            ['id' => 32, 'name' => 'Insurance Brokers', 'slug' => 'insurance-brokers', 'parent_id' => 3, 'icon' => 'fas fa-user-tie', 'color' => '#007bff', 'sort_order' => 2],
            ['id' => 33, 'name' => 'Lawyers & Legal Advisors', 'slug' => 'lawyers-legal-advisors', 'parent_id' => 3, 'icon' => 'fas fa-gavel', 'color' => '#007bff', 'sort_order' => 3],
            ['id' => 34, 'name' => 'Translators & Interpreters', 'slug' => 'translators-interpreters', 'parent_id' => 3, 'icon' => 'fas fa-language', 'color' => '#007bff', 'sort_order' => 4],
            ['id' => 35, 'name' => 'Real Estate Agents', 'slug' => 'real-estate-agents', 'parent_id' => 3, 'icon' => 'fas fa-sign', 'color' => '#007bff', 'sort_order' => 5],
            ['id' => 36, 'name' => 'Marketing & Advertising', 'slug' => 'marketing-advertising', 'parent_id' => 3, 'icon' => 'fas fa-bullhorn', 'color' => '#007bff', 'sort_order' => 6],
            ['id' => 78, 'name' => 'HR & Recruiting', 'slug' => 'hr-recruiting', 'parent_id' => 3, 'icon' => 'fas fa-users-cog', 'color' => '#007bff', 'sort_order' => 7],
            ['id' => 79, 'name' => 'IT Support', 'slug' => 'it-support', 'parent_id' => 3, 'icon' => 'fas fa-server', 'color' => '#007bff', 'sort_order' => 8],
            ['id' => 80, 'name' => 'Web Design', 'slug' => 'web-design', 'parent_id' => 3, 'icon' => 'fas fa-globe', 'color' => '#007bff', 'sort_order' => 9],
            ['id' => 81, 'name' => 'Graphic Design', 'slug' => 'graphic-design', 'parent_id' => 3, 'icon' => 'fas fa-pen-nib', 'color' => '#007bff', 'sort_order' => 10],
            ['id' => 82, 'name' => 'Notary Public', 'slug' => 'notary-public', 'parent_id' => 3, 'icon' => 'fas fa-stamp', 'color' => '#007bff', 'sort_order' => 11],
            ['id' => 83, 'name' => 'Printing Services', 'slug' => 'printing-services', 'parent_id' => 3, 'icon' => 'fas fa-print', 'color' => '#007bff', 'sort_order' => 12],

            // Personal & Lifestyle Services (Section 4) - 12 services (9 original + 3 new)
            ['id' => 38, 'name' => 'Beauty & Personal Care', 'slug' => 'beauty-personal-care', 'parent_id' => 4, 'icon' => 'fas fa-spa', 'color' => '#fd7e14', 'sort_order' => 1],
            ['id' => 39, 'name' => 'Restaurants & Catering', 'slug' => 'restaurants-catering', 'parent_id' => 4, 'icon' => 'fas fa-utensils', 'color' => '#fd7e14', 'sort_order' => 2],
            ['id' => 40, 'name' => 'Dental & Oral Care', 'slug' => 'dental-oral-care', 'parent_id' => 4, 'icon' => 'fas fa-tooth', 'color' => '#fd7e14', 'sort_order' => 3],
            ['id' => 41, 'name' => 'Fitness Trainers', 'slug' => 'fitness-trainers', 'parent_id' => 4, 'icon' => 'fas fa-dumbbell', 'color' => '#fd7e14', 'sort_order' => 4],
            ['id' => 42, 'name' => 'Massage Therapy', 'slug' => 'massage-therapy', 'parent_id' => 4, 'icon' => 'fas fa-hands', 'color' => '#fd7e14', 'sort_order' => 5],
            ['id' => 43, 'name' => 'Hair Stylists', 'slug' => 'hair-stylists', 'parent_id' => 4, 'icon' => 'fas fa-cut', 'color' => '#fd7e14', 'sort_order' => 6],
            ['id' => 44, 'name' => 'Makeup Artists', 'slug' => 'makeup-artists', 'parent_id' => 4, 'icon' => 'fas fa-palette', 'color' => '#fd7e14', 'sort_order' => 7],
            ['id' => 46, 'name' => 'Barber', 'slug' => 'barber', 'parent_id' => 4, 'icon' => 'fas fa-scissors', 'color' => '#fd7e14', 'sort_order' => 8],
            ['id' => 84, 'name' => 'Tattoo & Piercing Artists', 'slug' => 'tattoo-piercing-artists', 'parent_id' => 4, 'icon' => 'fas fa-paint-brush', 'color' => '#fd7e14', 'sort_order' => 9],
            ['id' => 85, 'name' => 'Pet Grooming', 'slug' => 'pet-grooming', 'parent_id' => 4, 'icon' => 'fas fa-paw', 'color' => '#fd7e14', 'sort_order' => 10],
            ['id' => 86, 'name' => 'Childcare / Babysitting', 'slug' => 'childcare-babysitting', 'parent_id' => 4, 'icon' => 'fas fa-baby', 'color' => '#fd7e14', 'sort_order' => 11],

            // Technical & Repair Services (Section 5) - 6 services
            ['id' => 49, 'name' => 'Computer Repair', 'slug' => 'computer-repair', 'parent_id' => 5, 'icon' => 'fas fa-desktop', 'color' => '#6f42c1', 'sort_order' => 1],
            ['id' => 50, 'name' => 'Phone Repair', 'slug' => 'phone-repair', 'parent_id' => 5, 'icon' => 'fas fa-mobile-alt', 'color' => '#6f42c1', 'sort_order' => 2],
            ['id' => 51, 'name' => 'AC & Refrigeration', 'slug' => 'ac-refrigeration', 'parent_id' => 5, 'icon' => 'fas fa-snowflake', 'color' => '#6f42c1', 'sort_order' => 3],
            ['id' => 52, 'name' => 'Generator Repair', 'slug' => 'generator-repair', 'parent_id' => 5, 'icon' => 'fas fa-bolt', 'color' => '#6f42c1', 'sort_order' => 4],
            ['id' => 87, 'name' => 'TV & Streaming Services', 'slug' => 'tv-streaming-services', 'parent_id' => 5, 'icon' => 'fas fa-tv', 'color' => '#6f42c1', 'sort_order' => 5],
            ['id' => 88, 'name' => 'Electronics Repair & Maintenance', 'slug' => 'electronics-repair-maintenance', 'parent_id' => 5, 'icon' => 'fas fa-microchip', 'color' => '#6f42c1', 'sort_order' => 6],

            // Event & Entertainment Services (Section 6) - 7 services (unchanged)
            ['id' => 54, 'name' => 'Photographers', 'slug' => 'photographers', 'parent_id' => 6, 'icon' => 'fas fa-camera', 'color' => '#e83e8c', 'sort_order' => 1],
            ['id' => 55, 'name' => 'Videographers', 'slug' => 'videographers', 'parent_id' => 6, 'icon' => 'fas fa-video', 'color' => '#e83e8c', 'sort_order' => 2],
            ['id' => 56, 'name' => 'DJs & Music', 'slug' => 'djs-music', 'parent_id' => 6, 'icon' => 'fas fa-music', 'color' => '#e83e8c', 'sort_order' => 3],
            ['id' => 57, 'name' => 'Catering Services', 'slug' => 'catering-services', 'parent_id' => 6, 'icon' => 'fas fa-utensils', 'color' => '#e83e8c', 'sort_order' => 4],
            ['id' => 58, 'name' => 'Decorators', 'slug' => 'decorators', 'parent_id' => 6, 'icon' => 'fas fa-palette', 'color' => '#e83e8c', 'sort_order' => 5],
            ['id' => 59, 'name' => 'Event Planners', 'slug' => 'event-planners', 'parent_id' => 6, 'icon' => 'fas fa-calendar-alt', 'color' => '#e83e8c', 'sort_order' => 6],
            ['id' => 60, 'name' => 'Entertainers', 'slug' => 'entertainers', 'parent_id' => 6, 'icon' => 'fas fa-theater-masks', 'color' => '#e83e8c', 'sort_order' => 7],

            // Others Category (under Others Section - ID 62)
            ['id' => 63, 'name' => 'Others', 'slug' => 'others-subcategory', 'parent_id' => 62, 'icon' => null, 'color' => '#6c757d', 'sort_order' => 1, 'description' => 'Other miscellaneous services'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert(array_merge($category, [
                'is_section' => 0,
                'is_active' => 1,
                'description' => $category['description'] ?? 'Professional ' . $category['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'meta_title' => $category['id'] == 63 ? null : $category['name'] . ' | Professional Services',
                'meta_description' => $category['description'] ?? 'Professional ' . $category['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                'created_at' => $category['id'] == 63 ? '2025-12-03 12:47:34' : $timestamp,
                'updated_at' => $category['id'] == 63 ? '2025-12-03 12:47:34' : $timestamp,
            ]));
        }

        // Reset AUTO_INCREMENT (MySQL/MariaDB only)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE categories AUTO_INCREMENT = 89');
        }

        $this->command->info('✅ Categories seeded successfully!');
        $this->command->info('   7 Sections + 65 Categories = 72 Total');
        $this->command->info('   - Automotive: 10 services (removed Towing, Winching, Jump Start)');
        $this->command->info('   - Home & Property: 21 services (replaced Window & Door with Repairs And Maintenance, removed Gutter)');
        $this->command->info('   - Professional: 12 services');
        $this->command->info('   - Personal: 11 services (removed Event Planner)');
        $this->command->info('   - Technical: 6 services (added TV & Streaming, Electronics Repair)');
        $this->command->info('   - Event: 7 services');
        $this->command->info('   Others Section: ID 62, Others Category: ID 63');
    }
}
