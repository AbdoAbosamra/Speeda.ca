<?php
require 'bootstrap/app.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sp = \App\Models\ServiceProvider::find(2);
if ($sp) {
    echo "== SERVICE PROVIDER #2 ==\n";
    echo "Name: " . $sp->company_name . "\n";
    echo "Profile image (DB): " . ($sp->profile_image ?? 'NULL') . "\n";
    echo "Profile image URL (Accessor): " . $sp->profile_image_url . "\n";

    // Check if Storage facade works
    if ($sp->profile_image) {
        echo "Storage::url result: " . \Illuminate\Support\Facades\Storage::url($sp->profile_image) . "\n";
        echo "File exists ? " . (\Illuminate\Support\Facades\Storage::disk('public')->exists($sp->profile_image) ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "Service Provider #2 not found!\n";
}
?>
