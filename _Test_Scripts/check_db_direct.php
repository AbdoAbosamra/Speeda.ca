<?php

// Simple database check without Laravel bootstrapping
$servername = "127.0.0.1";
$username = "root";
$password = "07775000";
$dbname = "speeda";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "\n=== DATABASE CHECK ===\n\n";

    // Check total service providers
    $result = $conn->query("SELECT COUNT(*) as total FROM service_providers");
    $total = $result->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total Service Providers: $total\n";

    // Check with images
    $result = $conn->query("SELECT COUNT(*) as count FROM service_providers WHERE profile_image IS NOT NULL AND profile_image != ''");
    $with_images = $result->fetch(PDO::FETCH_ASSOC)['count'];
    echo "With profile_image: $with_images\n";

    // Get first 5
    echo "\n=== FIRST 5 SERVICE PROVIDERS ===\n";
    $result = $conn->query("SELECT id, company_name, profile_image FROM service_providers LIMIT 5");
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        echo "\nID: " . $row['id'] . "\n";
        echo "Name: " . $row['company_name'] . "\n";
        echo "Image: " . ($row['profile_image'] ?? 'NULL') . "\n";
    }

    // Check storage directory
    echo "\n\n=== STORAGE FILES ===\n";
    $storageDir = 'storage/app/public/profile-images';
    if (is_dir($storageDir)) {
        $files = array_diff(scandir($storageDir), ['.', '..', '.gitignore']);
        echo "Files in storage: " . count($files) . "\n";
        foreach (array_slice($files, 0, 5) as $file) {
            echo "  - $file\n";
        }
    }

} catch(PDOException $e) {
    echo "Connection Error: " . $e->getMessage();
}
?>
