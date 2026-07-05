<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

foreach(App\Models\Category::all() as $c) {
    echo $c->name . ' === ' . $c->slug . PHP_EOL;
}
