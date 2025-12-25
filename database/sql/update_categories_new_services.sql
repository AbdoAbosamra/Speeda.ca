-- ========================================
-- Speeda Database Updates - New Services
-- Date: 2025-12-14
-- ========================================

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS=0;

-- ========================================
-- 1. UPDATE EXISTING CATEGORIES
-- ========================================

-- Update Tire service name
UPDATE categories
SET
    name = 'Tire Balancing & Wheel Alignment',
    slug = 'tire-balancing-wheel-alignment',
    updated_at = NOW()
WHERE id = 10;

-- Update Accounting service name
UPDATE categories
SET
    name = 'Accounting & Bookkeeping + Tax Preparation',
    slug = 'accounting-bookkeeping-tax-preparation',
    updated_at = NOW()
WHERE id = 31;

-- ========================================
-- 2. REMOVE APPLIANCE REPAIR FROM TECHNICAL SECTION
-- ========================================

DELETE FROM categories WHERE id = 48;

-- ========================================
-- 3. INSERT NEW AUTOMOTIVE SERVICES (Section 1)
-- ========================================

INSERT INTO categories (id, name, slug, parent_id, icon, color, sort_order, is_section, is_active, description, meta_title, meta_description, created_at, updated_at) VALUES
(64, 'Towing Services', 'towing-services', 1, 'fas fa-truck-pickup', '#dc3545', 9, 0, 1, 'Professional Towing Services in Laval, Montreal, Ottawa, Gatineau.', 'Towing Services | Professional Services', 'Professional Towing Services in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(65, 'Lockout Service', 'lockout-service', 1, 'fas fa-key', '#dc3545', 10, 0, 1, 'Professional Lockout Service in Laval, Montreal, Ottawa, Gatineau.', 'Lockout Service | Professional Services', 'Professional Lockout Service in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(66, 'Winching / Vehicle Recovery', 'winching-vehicle-recovery', 1, 'fas fa-anchor', '#dc3545', 11, 0, 1, 'Professional Winching / Vehicle Recovery in Laval, Montreal, Ottawa, Gatineau.', 'Winching / Vehicle Recovery | Professional Services', 'Professional Winching / Vehicle Recovery in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(67, 'Jump Start (Battery Boost)', 'jump-start-battery-boost', 1, 'fas fa-car-battery', '#dc3545', 12, 0, 1, 'Professional Jump Start (Battery Boost) in Laval, Montreal, Ottawa, Gatineau.', 'Jump Start (Battery Boost) | Professional Services', 'Professional Jump Start (Battery Boost) in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(68, 'Roadside Assistance (24/7)', 'roadside-assistance-24-7', 1, 'fas fa-ambulance', '#dc3545', 13, 0, 1, 'Professional Roadside Assistance (24/7) in Laval, Montreal, Ottawa, Gatineau.', 'Roadside Assistance (24/7) | Professional Services', 'Professional Roadside Assistance (24/7) in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW());

-- ========================================
-- 4. INSERT NEW HOME & PROPERTY SERVICES (Section 2)
-- ========================================

INSERT INTO categories (id, name, slug, parent_id, icon, color, sort_order, is_section, is_active, description, meta_title, meta_description, created_at, updated_at) VALUES
(69, 'Appliance Repair', 'appliance-repair-home', 2, 'fas fa-blender', '#28a745', 15, 0, 1, 'Professional Appliance Repair in Laval, Montreal, Ottawa, Gatineau.', 'Appliance Repair | Professional Services', 'Professional Appliance Repair in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(70, 'Flooring Installation & Repair', 'flooring-installation-repair', 2, 'fas fa-layer-group', '#28a745', 16, 0, 1, 'Professional Flooring Installation & Repair in Laval, Montreal, Ottawa, Gatineau.', 'Flooring Installation & Repair | Professional Services', 'Professional Flooring Installation & Repair in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(71, 'Window & Door Installation / Repair', 'window-door-installation-repair', 2, 'fas fa-door-open', '#28a745', 17, 0, 1, 'Professional Window & Door Installation / Repair in Laval, Montreal, Ottawa, Gatineau.', 'Window & Door Installation / Repair | Professional Services', 'Professional Window & Door Installation / Repair in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(72, 'Gutter Cleaning & Installation', 'gutter-cleaning-installation', 2, 'fas fa-water', '#28a745', 18, 0, 1, 'Professional Gutter Cleaning & Installation in Laval, Montreal, Ottawa, Gatineau.', 'Gutter Cleaning & Installation | Professional Services', 'Professional Gutter Cleaning & Installation in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(73, 'Fencing Installation & Repair', 'fencing-installation-repair', 2, 'fas fa-border-style', '#28a745', 19, 0, 1, 'Professional Fencing Installation & Repair in Laval, Montreal, Ottawa, Gatineau.', 'Fencing Installation & Repair | Professional Services', 'Professional Fencing Installation & Repair in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(74, 'Junk Removal', 'junk-removal', 2, 'fas fa-trash', '#28a745', 20, 0, 1, 'Professional Junk Removal in Laval, Montreal, Ottawa, Gatineau.', 'Junk Removal | Professional Services', 'Professional Junk Removal in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(75, 'Water Damage Restoration', 'water-damage-restoration', 2, 'fas fa-tint', '#28a745', 21, 0, 1, 'Professional Water Damage Restoration in Laval, Montreal, Ottawa, Gatineau.', 'Water Damage Restoration | Professional Services', 'Professional Water Damage Restoration in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(76, 'Garage Door Installation & Repair', 'garage-door-installation-repair', 2, 'fas fa-garage', '#28a745', 22, 0, 1, 'Professional Garage Door Installation & Repair in Laval, Montreal, Ottawa, Gatineau.', 'Garage Door Installation & Repair | Professional Services', 'Professional Garage Door Installation & Repair in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(77, 'General Contractor', 'general-contractor', 2, 'fas fa-hard-hat', '#28a745', 23, 0, 1, 'Professional General Contractor in Laval, Montreal, Ottawa, Gatineau.', 'General Contractor | Professional Services', 'Professional General Contractor in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW());

-- ========================================
-- 5. INSERT NEW PROFESSIONAL & BUSINESS SERVICES (Section 3)
-- ========================================

INSERT INTO categories (id, name, slug, parent_id, icon, color, sort_order, is_section, is_active, description, meta_title, meta_description, created_at, updated_at) VALUES
(78, 'HR & Recruiting', 'hr-recruiting', 3, 'fas fa-users-cog', '#007bff', 7, 0, 1, 'Professional HR & Recruiting in Laval, Montreal, Ottawa, Gatineau.', 'HR & Recruiting | Professional Services', 'Professional HR & Recruiting in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(79, 'IT Support', 'it-support', 3, 'fas fa-server', '#007bff', 8, 0, 1, 'Professional IT Support in Laval, Montreal, Ottawa, Gatineau.', 'IT Support | Professional Services', 'Professional IT Support in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(80, 'Web Design', 'web-design', 3, 'fas fa-globe', '#007bff', 9, 0, 1, 'Professional Web Design in Laval, Montreal, Ottawa, Gatineau.', 'Web Design | Professional Services', 'Professional Web Design in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(81, 'Graphic Design', 'graphic-design', 3, 'fas fa-pen-nib', '#007bff', 10, 0, 1, 'Professional Graphic Design in Laval, Montreal, Ottawa, Gatineau.', 'Graphic Design | Professional Services', 'Professional Graphic Design in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(82, 'Notary Public', 'notary-public', 3, 'fas fa-stamp', '#007bff', 11, 0, 1, 'Professional Notary Public in Laval, Montreal, Ottawa, Gatineau.', 'Notary Public | Professional Services', 'Professional Notary Public in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(83, 'Printing Services', 'printing-services', 3, 'fas fa-print', '#007bff', 12, 0, 1, 'Professional Printing Services in Laval, Montreal, Ottawa, Gatineau.', 'Printing Services | Professional Services', 'Professional Printing Services in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW());

-- ========================================
-- 6. INSERT NEW PERSONAL & LIFESTYLE SERVICES (Section 4)
-- ========================================

INSERT INTO categories (id, name, slug, parent_id, icon, color, sort_order, is_section, is_active, description, meta_title, meta_description, created_at, updated_at) VALUES
(84, 'Tattoo & Piercing Artists', 'tattoo-piercing-artists', 4, 'fas fa-paint-brush', '#fd7e14', 10, 0, 1, 'Professional Tattoo & Piercing Artists in Laval, Montreal, Ottawa, Gatineau.', 'Tattoo & Piercing Artists | Professional Services', 'Professional Tattoo & Piercing Artists in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(85, 'Pet Grooming', 'pet-grooming', 4, 'fas fa-paw', '#fd7e14', 11, 0, 1, 'Professional Pet Grooming in Laval, Montreal, Ottawa, Gatineau.', 'Pet Grooming | Professional Services', 'Professional Pet Grooming in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW()),
(86, 'Childcare / Babysitting', 'childcare-babysitting', 4, 'fas fa-baby', '#fd7e14', 12, 0, 1, 'Professional Childcare / Babysitting in Laval, Montreal, Ottawa, Gatineau.', 'Childcare / Babysitting | Professional Services', 'Professional Childcare / Babysitting in Laval, Montreal, Ottawa, Gatineau.', NOW(), NOW());

-- ========================================
-- 7. UPDATE SORT ORDERS FOR TECHNICAL SERVICES
-- ========================================

UPDATE categories SET sort_order = 1 WHERE id = 49; -- Computer Repair
UPDATE categories SET sort_order = 2 WHERE id = 50; -- Phone Repair
UPDATE categories SET sort_order = 3 WHERE id = 51; -- AC & Refrigeration
UPDATE categories SET sort_order = 4 WHERE id = 52; -- Generator Repair

-- ========================================
-- 8. UPDATE AUTO_INCREMENT
-- ========================================

ALTER TABLE categories AUTO_INCREMENT = 87;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- ========================================
-- VERIFICATION QUERIES
-- ========================================

-- Count total categories (should be 68)
SELECT COUNT(*) as total_categories FROM categories WHERE is_section = 0;

-- Count total sections (should be 7)
SELECT COUNT(*) as total_sections FROM categories WHERE is_section = 1;

-- List new services
SELECT id, name, parent_id FROM categories WHERE id >= 64 AND id <= 86 ORDER BY parent_id, sort_order;

-- Verify updated services
SELECT id, name, slug FROM categories WHERE id IN (10, 31);

-- ========================================
-- SUCCESS MESSAGE
-- ========================================

SELECT '✅ Database updated successfully!' as status,
       '23 new services added' as new_services,
       '2 services updated' as updated_services,
       '1 service moved' as moved_services;
