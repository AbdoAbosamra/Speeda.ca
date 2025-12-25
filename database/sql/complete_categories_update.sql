-- ===============================================
-- تحديث شامل لجميع الفئات والخدمات
-- بناءً على التصميم النهائي المطلوب
-- ===============================================

SET FOREIGN_KEY_CHECKS=0;

-- مسح البيانات القديمة
TRUNCATE TABLE `service_provider_categories`;
TRUNCATE TABLE `categories`;

SET FOREIGN_KEY_CHECKS=1;

-- ===============================================
-- الأقسام الرئيسية (7 أقسام)
-- ===============================================

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `created_at`, `updated_at`) VALUES
(1, 'Automotive Services', 'automotive-services', 'All automotive and vehicle services', 'fas fa-car', '#dc3545', 1, 1, NULL, 1, '2025-11-25 16:17:30', '2025-11-25 16:17:30'),
(2, 'Home & Property Services', 'home-property-services', 'Home maintenance and property services', 'fas fa-home', '#28a745', 1, 2, NULL, 1, '2025-11-25 16:17:30', '2025-11-25 16:17:30'),
(3, 'Professional & Business Services', 'professional-business-services', 'Professional and business support services', 'fas fa-briefcase', '#007bff', 1, 3, NULL, 1, '2025-11-25 16:17:30', '2025-11-25 16:17:30'),
(4, 'Personal & Lifestyle Services', 'personal-lifestyle-services', 'Personal care and lifestyle services', 'fas fa-heart', '#fd7e14', 1, 4, NULL, 1, '2025-11-25 16:17:30', '2025-11-25 16:17:30'),
(5, 'Technical & Repair Services', 'technical-repair-services', 'Technical and electronics repair services', 'fas fa-tools', '#6f42c1', 1, 5, NULL, 1, '2025-11-25 16:17:30', '2025-11-25 16:17:30'),
(6, 'Event & Entertainment Services', 'event-entertainment-services', 'Events and entertainment services', 'fas fa-glass-cheers', '#e83e8c', 1, 6, NULL, 1, '2025-11-25 16:17:30', '2025-11-25 16:17:30'),
(7, 'Others', 'others-section', 'Other miscellaneous services', 'fas fa-ellipsis-h', '#6c757d', 1, 7, NULL, 1, '2025-12-15 00:00:00', '2025-12-15 00:00:00');

-- ===============================================
-- 1. Automotive Services (13 خدمة)
-- ===============================================

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `created_at`, `updated_at`) VALUES
-- الخدمات الأساسية
(101, 'Tire Balancing & Wheel Alignment', 'tire-balancing-wheel-alignment', 'Professional tire balancing and wheel alignment services', 'fas fa-circle-notch', '#dc3545', 1, 1, 1, 0, NOW(), NOW()),
(102, 'Towing & Winching services', 'towing-winching-services', 'Emergency towing and winching services', 'fas fa-truck-pickup', '#dc3545', 1, 2, 1, 0, NOW(), NOW()),
(103, 'Winching / Vehicle Recovery', 'winching-vehicle-recovery', 'Vehicle recovery and winching services', 'fas fa-anchor', '#dc3545', 1, 3, 1, 0, NOW(), NOW()),
(104, 'Roadside Assistance (24/7)', 'roadside-assistance-24-7', 'Round the clock roadside assistance', 'fas fa-life-ring', '#dc3545', 1, 4, 1, 0, NOW(), NOW()),

-- خدمات الصيانة والإصلاح
(105, 'Appliance Repair', 'appliance-repair-auto', 'Automotive appliance repair services', 'fas fa-wrench', '#dc3545', 1, 5, 1, 0, NOW(), NOW()),
(106, 'Home Repairs & Maintenance', 'home-repairs-maintenance-auto', 'Mobile home repair services for vehicles', 'fas fa-tools', '#dc3545', 1, 6, 1, 0, NOW(), NOW()),
(107, 'Junk Removal', 'junk-removal-auto', 'Vehicle junk removal services', 'fas fa-trash-alt', '#dc3545', 1, 7, 1, 0, NOW(), NOW()),
(108, 'Water Damage Restoration', 'water-damage-restoration-auto', 'Vehicle water damage restoration', 'fas fa-water', '#dc3545', 1, 8, 1, 0, NOW(), NOW()),
(109, 'Garage Door Installation & Repair', 'garage-door-installation-repair', 'Garage door services for vehicle storage', 'fas fa-garage', '#dc3545', 1, 9, 1, 0, NOW(), NOW()),
(110, 'General Contractor', 'general-contractor-auto', 'General automotive contracting services', 'fas fa-hard-hat', '#dc3545', 1, 10, 1, 0, NOW(), NOW());

-- ===============================================
-- 2. Home & Property Services (9 خدمات)
-- ===============================================

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `created_at`, `updated_at`) VALUES
(201, 'Appliance Repair', 'appliance-repair-home', 'Home appliance repair and maintenance', 'fas fa-blender', '#28a745', 1, 1, 2, 0, NOW(), NOW()),
(202, 'Home Repairs & Maintenance', 'home-repairs-maintenance', 'General home repair and maintenance services', 'fas fa-hammer', '#28a745', 1, 2, 2, 0, NOW(), NOW()),
(203, 'Junk Removal', 'junk-removal-home', 'Residential junk removal services', 'fas fa-dumpster', '#28a745', 1, 3, 2, 0, NOW(), NOW()),
(204, 'Water Damage Restoration', 'water-damage-restoration-home', 'Water damage restoration for homes', 'fas fa-house-flood', '#28a745', 1, 4, 2, 0, NOW(), NOW()),
(205, 'Garage Door Installation & Repair', 'garage-door-home', 'Residential garage door services', 'fas fa-door-closed', '#28a745', 1, 5, 2, 0, NOW(), NOW()),
(206, 'General Contractor', 'general-contractor-home', 'General home contracting services', 'fas fa-user-hard-hat', '#28a745', 1, 6, 2, 0, NOW(), NOW());

-- ===============================================
-- 3. Professional & Business Services (7 خدمات)
-- ===============================================

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `created_at`, `updated_at`) VALUES
(301, 'Accounting & Bookkeeping + Tax Preparation', 'accounting-bookkeeping-tax', 'Professional accounting and tax services', 'fas fa-calculator', '#007bff', 1, 1, 3, 0, NOW(), NOW()),
(302, 'HR & Recruiting', 'hr-recruiting', 'Human resources and recruitment services', 'fas fa-users-cog', '#007bff', 1, 2, 3, 0, NOW(), NOW()),
(303, 'IT Support', 'it-support', 'Information technology support services', 'fas fa-laptop-code', '#007bff', 1, 3, 3, 0, NOW(), NOW()),
(304, 'Web Design', 'web-design', 'Professional web design and development', 'fas fa-globe', '#007bff', 1, 4, 3, 0, NOW(), NOW()),
(305, 'Graphic Design', 'graphic-design', 'Creative graphic design services', 'fas fa-palette', '#007bff', 1, 5, 3, 0, NOW(), NOW()),
(306, 'Notary Public', 'notary-public', 'Professional notary services', 'fas fa-stamp', '#007bff', 1, 6, 3, 0, NOW(), NOW()),
(307, 'Printing Services', 'printing-services', 'Professional printing services', 'fas fa-print', '#007bff', 1, 7, 3, 0, NOW(), NOW());

-- ===============================================
-- 4. Personal & Lifestyle Services (3 خدمات)
-- ===============================================

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `created_at`, `updated_at`) VALUES
(401, 'Tattoo & Piercing Artists', 'tattoo-piercing-artists', 'Professional tattoo and piercing services', 'fas fa-pen-fancy', '#fd7e14', 1, 1, 4, 0, NOW(), NOW()),
(402, 'Pet Grooming', 'pet-grooming', 'Professional pet grooming services', 'fas fa-paw', '#fd7e14', 1, 2, 4, 0, NOW(), NOW()),
(403, 'Childcare / Babysitting', 'childcare-babysitting', 'Professional childcare and babysitting', 'fas fa-baby', '#fd7e14', 1, 3, 4, 0, NOW(), NOW());

-- ===============================================
-- إعادة تعيين Auto Increment
-- ===============================================

ALTER TABLE `categories` AUTO_INCREMENT = 500;

-- ===============================================
-- التحقق من البيانات
-- ===============================================

-- عرض جميع الأقسام
SELECT id, name, slug, is_section, sort_order 
FROM categories 
WHERE is_section = 1 
ORDER BY sort_order;

-- عد الخدمات في كل قسم
SELECT 
    s.id,
    s.name AS section_name,
    COUNT(c.id) AS services_count
FROM categories s
LEFT JOIN categories c ON c.parent_id = s.id
WHERE s.is_section = 1
GROUP BY s.id, s.name
ORDER BY s.sort_order;

-- ===============================================
-- ملخص التحديث
-- ===============================================
-- الأقسام الرئيسية: 7
-- 1. Automotive Services: 10 خدمات
-- 2. Home & Property Services: 6 خدمات  
-- 3. Professional & Business Services: 7 خدمات
-- 4. Personal & Lifestyle Services: 3 خدمات
-- 5. Technical & Repair Services: (existing)
-- 6. Event & Entertainment Services: (existing)
-- 7. Others: (existing)
-- ===============================================
-- إجمالي الخدمات الجديدة المضافة: 26 خدمة
-- ===============================================
