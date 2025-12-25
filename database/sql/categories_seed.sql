-- =====================================================
-- Speeda Categories - Complete Database Seed
-- Generated: 2025-01-17 (CLEANED VERSION)
-- =====================================================
-- Total Records: 57 categories
-- - 7 Sections (parent_id = NULL, is_section = 1)
-- - 50 Subcategories (parent_id != NULL, is_section = 0)
--
-- Structure:
-- 1. Automotive Services (8 subcategories)
-- 2. Home & Property Services (14 subcategories)
-- 3. Professional & Business Services (6 subcategories)
-- 4. Personal & Lifestyle Services (9 subcategories)
-- 5. Technical & Repair Services (5 subcategories)
-- 6. Event & Entertainment Services (7 subcategories)
-- 7. Others Section (1 subcategory: Others)
-- =====================================================

-- ⚠️ WARNING: This file DELETES all existing categories!
-- Backup your database before running this command.
-- Use this for:
-- 1. Fresh installations
-- 2. Fixing duplicate "Others" issues
-- 3. Ensuring consistent structure across environments
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Delete all categories (including soft-deleted)
DELETE FROM `categories`;

-- Reset auto increment to start from 1
ALTER TABLE `categories` AUTO_INCREMENT = 1;


-- =====================================================
-- Insert Fresh Data
-- =====================================================

-- Insert Sections (parent categories with is_section = 1)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Automotive Services', 'automotive-services', NULL, 'fas fa-car', '#dc3545', 1, 1, NULL, 1, NULL, NULL, '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(2, 'Home & Property Services', 'home-property-services', NULL, 'fas fa-home', '#28a745', 1, 2, NULL, 1, NULL, NULL, '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(3, 'Professional & Business Services', 'professional-business-services', NULL, 'fas fa-briefcase', '#007bff', 1, 3, NULL, 1, NULL, NULL, '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(4, 'Personal & Lifestyle Services', 'personal-lifestyle-services', NULL, 'fas fa-heart', '#fd7e14', 1, 4, NULL, 1, NULL, NULL, '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(5, 'Technical & Repair Services', 'technical-repair-services', NULL, 'fas fa-tools', '#6f42c1', 1, 5, NULL, 1, NULL, NULL, '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(6, 'Event & Entertainment Services', 'event-entertainment-services', NULL, 'fas fa-glass-cheers', '#e83e8c', 1, 6, NULL, 1, NULL, NULL, '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(62, 'Others', 'others-1', 'Other services', NULL, '#6c757d', 1, 7, NULL, 1, NULL, NULL, '2025-12-03 12:43:56', '2025-12-03 12:43:56', NULL);

-- Insert Categories under Automotive Services (Section 1)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 'Car Mechanics', 'car-mechanics', 'Professional Car Mechanics services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-tools', '#dc3545', 1, 1, 1, 0, 'Car Mechanics | Professional Services', 'Professional Car Mechanics services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(8, 'Oil Change Services', 'oil-change-services', 'Professional Oil Change Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-oil-can', '#dc3545', 1, 2, 1, 0, 'Oil Change Services | Professional Services', 'Professional Oil Change Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(9, 'Electric & Hybrid Car Services', 'electric-hybrid-car-services', 'Professional Electric & Hybrid Car Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-car-battery', '#dc3545', 1, 3, 1, 0, 'Electric & Hybrid Car Services | Professional Services', 'Professional Electric & Hybrid Car Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(10, 'Tire Change & Repair', 'tire-change-repair', 'Professional Tire Change & Repair services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-tire', '#dc3545', 1, 4, 1, 0, 'Tire Change & Repair | Professional Services', 'Professional Tire Change & Repair services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(11, 'Car Dealers', 'car-dealers', 'Professional Car Dealers services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-car-side', '#dc3545', 1, 5, 1, 0, 'Car Dealers | Professional Services', 'Professional Car Dealers services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(12, 'Cars Inspections (Safety) for Uber', 'cars-inspections-safety-for-uber', 'Professional Cars Inspections (Safety) for Uber services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-clipboard-check', '#dc3545', 1, 6, 1, 0, 'Cars Inspections (Safety) for Uber | Professional Services', 'Professional Cars Inspections (Safety) for Uber services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(13, 'Auto Body Repair', 'auto-body-repair', 'Professional Auto Body Repair services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-hammer', '#dc3545', 1, 7, 1, 0, 'Auto Body Repair | Professional Services', 'Professional Auto Body Repair services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(14, 'Car Wash & Detailing', 'car-wash-detailing', 'Professional Car Wash & Detailing services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-soap', '#dc3545', 1, 8, 1, 0, 'Car Wash & Detailing | Professional Services', 'Professional Car Wash & Detailing services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL);

-- Insert Categories under Home & Property Services (Section 2)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(16, 'Roofing Contractors', 'roofing-contractors', 'Professional Roofing Contractors services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-house-damage', '#28a745', 1, 1, 2, 0, 'Roofing Contractors | Professional Services', 'Professional Roofing Contractors services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(17, 'Carpentry Services', 'carpentry-services', 'Professional Carpentry Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-hammer', '#28a745', 1, 2, 2, 0, 'Carpentry Services | Professional Services', 'Professional Carpentry Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(18, 'Painting Services', 'painting-services', 'Professional Painting Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-paint-roller', '#28a745', 1, 3, 2, 0, 'Painting Services | Professional Services', 'Professional Painting Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(19, 'Plumbing Services', 'plumbing-services', 'Professional Plumbing Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-faucet', '#28a745', 1, 4, 2, 0, 'Plumbing Services | Professional Services', 'Professional Plumbing Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(20, 'Electrical Technicians', 'electrical-technicians', 'Professional Electrical Technicians services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-bolt', '#28a745', 1, 5, 2, 0, 'Electrical Technicians | Professional Services', 'Professional Electrical Technicians services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(21, 'Handyman Services', 'handyman-services', 'Professional Handyman Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-toolbox', '#28a745', 1, 6, 2, 0, 'Handyman Services | Professional Services', 'Professional Handyman Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(22, 'Moving Services', 'moving-services', 'Professional Moving Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-truck-moving', '#28a745', 1, 7, 2, 0, 'Moving Services | Professional Services', 'Professional Moving Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(23, 'Cleaning Services', 'cleaning-services', 'Professional Cleaning Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-broom', '#28a745', 1, 8, 2, 0, 'Cleaning Services | Professional Services', 'Professional Cleaning Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(24, 'Landscaping & Gardening', 'landscaping-gardening', 'Professional Landscaping & Gardening services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-leaf', '#28a745', 1, 9, 2, 0, 'Landscaping & Gardening | Professional Services', 'Professional Landscaping & Gardening services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(25, 'Home Renovation', 'home-renovation', 'Professional Home Renovation services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-paint-brush', '#28a745', 1, 10, 2, 0, 'Home Renovation | Professional Services', 'Professional Home Renovation services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(26, 'Pest Control', 'pest-control', 'Professional Pest Control services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-bug', '#28a745', 1, 11, 2, 0, 'Pest Control | Professional Services', 'Professional Pest Control services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(27, 'Security System Installation', 'security-system-installation', 'Professional Security System Installation services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-shield-alt', '#28a745', 1, 12, 2, 0, 'Security System Installation | Professional Services', 'Professional Security System Installation services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(28, 'Snow Removal', 'snow-removal', 'Professional Snow Removal services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-snowflake', '#28a745', 1, 13, 2, 0, 'Snow Removal | Professional Services', 'Professional Snow Removal services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(29, 'HVAC Services', 'hvac-services', 'Professional HVAC Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-fan', '#28a745', 1, 14, 2, 0, 'HVAC Services | Professional Services', 'Professional HVAC Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL);

-- Insert Categories under Professional & Business Services (Section 3)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(31, 'Accounting & Bookkeeping', 'accounting-bookkeeping', 'Professional Accounting & Bookkeeping services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-calculator', '#007bff', 1, 1, 3, 0, 'Accounting & Bookkeeping | Professional Services', 'Professional Accounting & Bookkeeping services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(32, 'Insurance Brokers', 'insurance-brokers', 'Professional Insurance Brokers services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-user-tie', '#007bff', 1, 2, 3, 0, 'Insurance Brokers | Professional Services', 'Professional Insurance Brokers services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(33, 'Lawyers & Legal Advisors', 'lawyers-legal-advisors', 'Professional Lawyers & Legal Advisors services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-gavel', '#007bff', 1, 3, 3, 0, 'Lawyers & Legal Advisors | Professional Services', 'Professional Lawyers & Legal Advisors services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(34, 'Translators & Interpreters', 'translators-interpreters', 'Professional Translators & Interpreters services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-language', '#007bff', 1, 4, 3, 0, 'Translators & Interpreters | Professional Services', 'Professional Translators & Interpreters services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(35, 'Real Estate Agents', 'real-estate-agents', 'Professional Real Estate Agents services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-sign', '#007bff', 1, 5, 3, 0, 'Real Estate Agents | Professional Services', 'Professional Real Estate Agents services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(36, 'Marketing & Advertising', 'marketing-advertising', 'Professional Marketing & Advertising services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-bullhorn', '#007bff', 1, 6, 3, 0, 'Marketing & Advertising | Professional Services', 'Professional Marketing & Advertising services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL);

-- Insert Categories under Personal & Lifestyle Services (Section 4)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(38, 'Beauty & Personal Care', 'beauty-personal-care', 'Professional Beauty & Personal Care services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-spa', '#fd7e14', 1, 1, 4, 0, 'Beauty & Personal Care | Professional Services', 'Professional Beauty & Personal Care services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(39, 'Restaurants & Catering', 'restaurants-catering', 'Professional Restaurants & Catering services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-utensils', '#fd7e14', 1, 2, 4, 0, 'Restaurants & Catering | Professional Services', 'Professional Restaurants & Catering services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(40, 'Dental & Oral Care', 'dental-oral-care', 'Professional Dental & Oral Care services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-tooth', '#fd7e14', 1, 3, 4, 0, 'Dental & Oral Care | Professional Services', 'Professional Dental & Oral Care services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(41, 'Fitness Trainers', 'fitness-trainers', 'Professional Fitness Trainers services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-dumbbell', '#fd7e14', 1, 4, 4, 0, 'Fitness Trainers | Professional Services', 'Professional Fitness Trainers services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(42, 'Massage Therapy', 'massage-therapy', 'Professional Massage Therapy services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-hands', '#fd7e14', 1, 5, 4, 0, 'Massage Therapy | Professional Services', 'Professional Massage Therapy services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(43, 'Hair Stylists', 'hair-stylists', 'Professional Hair Stylists services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-cut', '#fd7e14', 1, 6, 4, 0, 'Hair Stylists | Professional Services', 'Professional Hair Stylists services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(44, 'Makeup Artists', 'makeup-artists', 'Professional Makeup Artists services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-palette', '#fd7e14', 1, 7, 4, 0, 'Makeup Artists | Professional Services', 'Professional Makeup Artists services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(45, 'Event Planners', 'event-planners', 'Professional Event Planners services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-calendar-alt', '#fd7e14', 1, 8, 4, 0, 'Event Planners | Professional Services', 'Professional Event Planners services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(46, 'Barber', 'barber', 'Professional Barber services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-scissors', '#fd7e14', 1, 9, 4, 0, 'Barber | Professional Services', 'Professional Barber services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL);

-- Insert Categories under Technical & Repair Services (Section 5)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(48, 'Appliance Repair', 'appliance-repair', 'Professional Appliance Repair services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-blender', '#6f42c1', 1, 1, 5, 0, 'Appliance Repair | Professional Services', 'Professional Appliance Repair services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(49, 'Computer Repair', 'computer-repair', 'Professional Computer Repair services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-desktop', '#6f42c1', 1, 2, 5, 0, 'Computer Repair | Professional Services', 'Professional Computer Repair services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(50, 'Phone Repair', 'phone-repair', 'Professional Phone Repair services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-mobile-alt', '#6f42c1', 1, 3, 5, 0, 'Phone Repair | Professional Services', 'Professional Phone Repair services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(51, 'AC & Refrigeration', 'ac-refrigeration', 'Professional AC & Refrigeration services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-snowflake', '#6f42c1', 1, 4, 5, 0, 'AC & Refrigeration | Professional Services', 'Professional AC & Refrigeration services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(52, 'Generator Repair', 'generator-repair', 'Professional Generator Repair services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-bolt', '#6f42c1', 1, 5, 5, 0, 'Generator Repair | Professional Services', 'Professional Generator Repair services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL);

-- Insert Categories under Event & Entertainment Services (Section 6)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(54, 'Photographers', 'photographers', 'Professional Photographers services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-camera', '#e83e8c', 1, 1, 6, 0, 'Photographers | Professional Services', 'Professional Photographers services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(55, 'Videographers', 'videographers', 'Professional Videographers services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-video', '#e83e8c', 1, 2, 6, 0, 'Videographers | Professional Services', 'Professional Videographers services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(56, 'DJs & Music', 'djs-music', 'Professional DJs & Music services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-music', '#e83e8c', 1, 3, 6, 0, 'DJs & Music | Professional Services', 'Professional DJs & Music services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(57, 'Catering Services', 'catering-services', 'Professional Catering Services services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-utensils', '#e83e8c', 1, 4, 6, 0, 'Catering Services | Professional Services', 'Professional Catering Services services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(58, 'Decorators', 'decorators', 'Professional Decorators services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-palette', '#e83e8c', 1, 5, 6, 0, 'Decorators | Professional Services', 'Professional Decorators services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(59, 'Event Planners', 'event-planners', 'Professional Event Planners services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-calendar-alt', '#e83e8c', 1, 6, 6, 0, 'Event Planners | Professional Services', 'Professional Event Planners services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL),
(60, 'Entertainers', 'entertainers', 'Professional Entertainers services in Laval, Montreal, Ottawa, Gatineau.', 'fas fa-theater-masks', '#e83e8c', 1, 7, 6, 0, 'Entertainers | Professional Services', 'Professional Entertainers services in Laval, Montreal, Ottawa, Gatineau.', '2025-11-25 16:17:30', '2025-11-25 16:17:30', NULL);

-- =====================================================
-- IMPORTANT: Others Section and Category
-- =====================================================
-- =====================================================
-- Others Section and Category
-- This is the standalone "Others" section (ID 62) with its child category (ID 63)
-- Service providers who select "other" profession will be assigned to category ID 63
-- =====================================================

-- Insert Others Category (child of Others section - ID 63)
-- This is where service providers with "other" profession are assigned
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(63, 'Others', 'others-subcategory', 'Other miscellaneous services', NULL, '#6c757d', 1, 1, 62, 0, NULL, NULL, '2025-12-03 12:47:34', '2025-12-03 12:47:34', NULL);

-- =====================================================
-- Reset AUTO_INCREMENT to next available ID
-- =====================================================
ALTER TABLE `categories` AUTO_INCREMENT = 64;

-- =====================================================
-- Verification Queries (run these to check the data)
-- =====================================================
-- SELECT * FROM categories WHERE is_section = 1 ORDER BY sort_order;
-- SELECT * FROM categories WHERE parent_id = 62;  -- Check Others category
-- SELECT COUNT(*) as total_sections FROM categories WHERE is_section = 1 AND deleted_at IS NULL;
-- SELECT COUNT(*) as total_categories FROM categories WHERE is_section = 0 AND deleted_at IS NULL;
-- SELECT COUNT(*) as total_all FROM categories WHERE deleted_at IS NULL;  -- Should be 57
