<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing Factory Classes...\n\n";

    // Test User Factory
    echo "1. Testing User Factory:\n";
    $user = \App\Models\User::factory()->create();
    echo "   ✓ User created with ID: {$user->id}\n";

    // Use existing location
    echo "2. Using existing Location:\n";
    $location = \App\Models\Location::find(1);
    echo "   ✓ Location found: {$location->city}\n";

    // Test Category Factory
    echo "3. Testing Category Factory:\n";
    $category = \App\Models\Category::factory()->create();
    echo "   ✓ Category created: {$category->name}\n";

    // Test ServiceProvider Factory
    echo "4. Testing ServiceProvider Factory:\n";
    $serviceProvider = \App\Models\ServiceProvider::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'location_id' => $location->id
    ]);
    echo "   ✓ ServiceProvider created with ID: {$serviceProvider->id}\n";

    // Test ServiceProviderProfile Factory
    echo "5. Testing ServiceProviderProfile Factory:\n";
    $profile = \App\Models\ServiceProviderProfile::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'location_id' => $location->id
    ]);
    echo "   ✓ ServiceProviderProfile created with ID: {$profile->id}\n";

    // Test Booking Factory
    echo "6. Testing Booking Factory:\n";
    $booking = \App\Models\Booking::factory()->create([
        'client_id' => $user->id,
        'service_provider_profile_id' => $profile->id,
        'service_provider_id' => $serviceProvider->id
    ]);
    echo "   ✓ Booking created with ID: {$booking->id}\n";

    echo "\n✅ All Factory classes working successfully!\n";

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
