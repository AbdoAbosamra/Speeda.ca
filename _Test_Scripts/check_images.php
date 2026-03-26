<?php
$mysqli = new mysqli("127.0.0.1", "root", "07775000", "speeda");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Query locations
echo "=== LOCATIONS TABLE ===\n";
$result = $mysqli->query("SELECT id, city, image FROM locations ORDER BY city");
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | City: " . $row['city'] . " | Image: " . ($row['image'] ? $row['image'] : 'NULL') . "\n";
}

// Query service providers
echo "\n=== SERVICE PROVIDERS WITH PROFILE IMAGES ===\n";
$result = $mysqli->query("SELECT id, company_name, profile_image FROM service_providers WHERE profile_image IS NOT NULL AND profile_image != '' LIMIT 15");
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Company: " . $row['company_name'] . " | Image: " . $row['profile_image'] . "\n";
}

// Check service provider profiles and portfolios
echo "\n=== SERVICE PROVIDER PROFILES ===\n";
$result = $mysqli->query("SELECT * FROM service_provider_profiles LIMIT 5");
if ($result && $result->num_rows > 0) {
    $fields = array();
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No service provider profiles found\n";
}

// Check portfolios table
echo "\n=== PORTFOLIOS TABLE ===\n";
$result = $mysqli->query("DESC portfolios");
while ($row = $result->fetch_assoc()) {
    echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
}

echo "\nPortfolio records:\n";
$result = $mysqli->query("SELECT * FROM portfolios LIMIT 10");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

// Check users table structure and data
echo "\n=== USERS TABLE STRUCTURE ===\n";
$result = $mysqli->query("DESC users");
while ($row = $result->fetch_assoc()) {
    echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
}

echo "\nUsers data:\n";
$result = $mysqli->query("SELECT * FROM users");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

// Check statistics - verify tables exist first
echo "\n=== IMAGE STATISTICS ===\n";
$result = $mysqli->query("SHOW TABLES");
$tables = array();
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}
echo "Available tables: " . implode(", ", $tables) . "\n";

$result = $mysqli->query("SELECT COUNT(*) as total FROM service_providers");
$row = $result->fetch_assoc();
echo "Total service providers: " . $row['total'] . "\n";

$result = $mysqli->query("SELECT COUNT(*) as total FROM users");
$row = $result->fetch_assoc();
echo "Total users: " . $row['total'] . "\n";

$result = $mysqli->query("SELECT COUNT(*) as total FROM locations");
$row = $result->fetch_assoc();
echo "Total locations: " . $row['total'] . "\n";

$mysqli->close();
?>
