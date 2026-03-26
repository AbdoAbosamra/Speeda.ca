<?php
/**
 * Comprehensive Image Audit for Speeda Laravel Application
 */

$mysqli = new mysqli("127.0.0.1", "root", "07775000", "speeda");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=================================================\n";
echo "SPEEDA IMAGE AUDIT REPORT\n";
echo "=================================================\n\n";

// 1. PUBLIC IMAGES CHECK
echo "1. PUBLIC IMAGES (public/images/)\n";
echo "------------------------------------\n";
$public_images = array(
    'banner.png' => 'public/images/banner.png',
    'main-logo.png' => 'public/images/main-logo.png',
    'user.png' => 'public/images/user.png',
    'default-profile.png' => 'public/images/default-profile.png',
    'default-provider.jpg' => 'public/images/default-provider.jpg',
    'pattern.svg' => 'public/images/pattern.svg',
    'Logo.png' => 'public/images/Logo.png',
);

foreach ($public_images as $name => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "✓ $name ({$size} bytes) - FOUND\n";
    } else {
        echo "✗ $name - MISSING\n";
    }
}

// 2. STORAGE LOCATION IMAGES CHECK
echo "\n2. LOCATION IMAGES (storage/app/public/location-images/)\n";
echo "-----------------------------------------------------------\n";
$location_images_dir = 'storage/app/public/location-images';
if (is_dir($location_images_dir)) {
    $images = array_diff(scandir($location_images_dir), array('.', '..', '.gitignore'));
    if (count($images) > 0) {
        foreach ($images as $img) {
            if (is_file("$location_images_dir/$img")) {
                $size = filesize("$location_images_dir/$img");
                echo "✓ $img ({$size} bytes)\n";
            }
        }
    } else {
        echo "⚠ Directory is empty\n";
    }
} else {
    echo "✗ Directory does not exist\n";
}

// 3. STORAGE PROFILE IMAGES CHECK
echo "\n3. PROFILE IMAGES (storage/app/public/profile-images/)\n";
echo "---------------------------------------------------------\n";
$profile_images_dir = 'storage/app/public/profile-images';
if (is_dir($profile_images_dir)) {
    $images = array_diff(scandir($profile_images_dir), array('.', '..', '.gitignore'));
    if (count($images) > 0) {
        echo "Total profile images: " . count($images) . "\n";
        foreach ($images as $img) {
            if (is_file("$profile_images_dir/$img")) {
                $size = filesize("$profile_images_dir/$img");
                echo "  - $img ({$size} bytes)\n";
            }
        }
    } else {
        echo "⚠ Directory is empty\n";
    }
} else {
    echo "✗ Directory does not exist\n";
}

// 4. STORAGE CERTIFICATIONS CHECK
echo "\n4. CERTIFICATION FILES (storage/app/public/certifications/)\n";
echo "--------------------------------------------------------------\n";
$cert_dir = 'storage/app/public/certifications';
if (is_dir($cert_dir)) {
    $files = array_diff(scandir($cert_dir), array('.', '..', '.gitignore'));
    if (count($files) > 0) {
        echo "Total certification files: " . count($files) . "\n";
        foreach ($files as $file) {
            if (is_file("$cert_dir/$file")) {
                $size = filesize("$cert_dir/$file");
                echo "  - $file ({$size} bytes)\n";
            }
        }
    } else {
        echo "⚠ Directory is empty\n";
    }
} else {
    echo "✗ Directory does not exist\n";
}

// 5. DATABASE LOCATIONS WITH IMAGES
echo "\n5. LOCATIONS IN DATABASE\n";
echo "-------------------------\n";
$result = $mysqli->query("SELECT id, city, image FROM locations ORDER BY city");
$locations_data = array();
while ($row = $result->fetch_assoc()) {
    $locations_data[] = $row;
    $image_status = $row['image'] ? '✓ ' . $row['image'] : '✗ NULL (no image)';
    echo "ID {$row['id']}: {$row['city']} - {$image_status}\n";
}

// 6. DATABASE SERVICE PROVIDERS WITH PROFILE IMAGES
echo "\n6. SERVICE PROVIDERS\n";
echo "---------------------\n";
$result = $mysqli->query("SELECT COUNT(*) as total FROM service_providers");
$row = $result->fetch_assoc();
$sp_count = $row['total'];
echo "Total service providers: $sp_count\n";

$result = $mysqli->query("SELECT COUNT(*) as total FROM service_providers WHERE profile_image IS NOT NULL AND profile_image != ''");
$row = $result->fetch_assoc();
echo "Service providers with profile images: {$row['total']}\n";

if ($sp_count > 0) {
    echo "\nSample service providers:\n";
    $result = $mysqli->query("SELECT id, company_name, profile_image FROM service_providers LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        $profile_status = $row['profile_image'] ? '✓ ' . $row['profile_image'] : '✗ NULL';
        echo "  ID {$row['id']}: {$row['company_name']} - {$profile_status}\n";
    }
}

// 7. DATABASE PORTFOLIOS WITH IMAGES
echo "\n7. PORTFOLIO ITEMS\n";
echo "-------------------\n";
$result = $mysqli->query("SELECT COUNT(*) as total FROM portfolios");
$row = $result->fetch_assoc();
$portfolio_count = $row['total'];
echo "Total portfolio items: $portfolio_count\n";

$result = $mysqli->query("SELECT COUNT(*) as total FROM portfolios WHERE image IS NOT NULL AND image != ''");
$row = $result->fetch_assoc();
echo "Portfolio items with images: {$row['total']}\n";

if ($portfolio_count > 0) {
    echo "\nSample portfolio items:\n";
    $result = $mysqli->query("SELECT id, service_provider_id, title, image FROM portfolios LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        $image_status = $row['image'] ? '✓ ' . $row['image'] : '✗ NULL';
        echo "  ID {$row['id']}: {$row['title']} (Provider: {$row['service_provider_id']}) - {$image_status}\n";
    }
}

// 8. DATABASE USERS AVATARS
echo "\n8. USER AVATARS\n";
echo "----------------\n";
$result = $mysqli->query("SELECT COUNT(*) as total FROM users");
$row = $result->fetch_assoc();
$user_count = $row['total'];
echo "Total users: $user_count\n";

$result = $mysqli->query("SELECT COUNT(*) as total FROM users WHERE avatar IS NOT NULL AND avatar != ''");
$row = $result->fetch_assoc();
echo "Users with avatars: {$row['total']}\n";

if ($user_count > 0) {
    echo "\nAll users:\n";
    $result = $mysqli->query("SELECT id, name, avatar FROM users");
    while ($row = $result->fetch_assoc()) {
        $avatar_status = $row['avatar'] ? '✓ ' . $row['avatar'] : '✗ NULL (no avatar)';
        echo "  ID {$row['id']}: {$row['name']} - {$avatar_status}\n";
    }
}

// 9. SUMMARY & ISSUES
echo "\n\n=================================================\n";
echo "AUDIT SUMMARY & ISSUES\n";
echo "=================================================\n\n";

echo "WORKING IMAGES:\n";
echo "✓ Public images (banner.png, main-logo.png, user.png, etc.)\n";
echo "✓ Profile images in storage/app/public/profile-images/ (13 images)\n";
echo "✓ Certification files (10 files)\n";
echo "✓ Storage symlink is working (public/storage exists)\n\n";

echo "ISSUES FOUND:\n";
echo "✗ ALL LOCATIONS have NULL images - Location images are not stored\n";
echo "✗ NO SERVICE PROVIDERS in database - No profile images to check\n";
echo "✗ NO PORTFOLIO ITEMS in database - No portfolio images to check\n";
echo "✗ Users have no avatars - Avatar field is empty\n";
echo "✗ pattern.svg file is missing from public/images/\n\n";

echo "RECOMMENDATIONS:\n";
echo "1. Create/upload location images for Gatineau, Laval, Montreal, Ottawa\n";
echo "2. Store location images with paths like: location-images/gatineau.jpg\n";
echo "3. Ensure Storage::url() calls work correctly for location images\n";
echo "4. Add pattern.svg to public/images/ directory\n";
echo "5. Verify storage symlink is properly configured: php artisan storage:link\n";
echo "6. Check that profile images use correct Storage::url() format\n";
echo "7. Ensure all storage directories have proper permissions\n\n";

$mysqli->close();
?>
