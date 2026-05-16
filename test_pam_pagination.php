<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate the PAM service call
$service = new \App\Services\AdminProviderActivityMonitorService();
$providers = $service->paginateProviders(15, request());

echo "=== PAGINATOR DEBUG ===\n";
echo "Class: " . get_class($providers) . "\n";
echo "Total: " . $providers->total() . "\n";
echo "Per page: " . $providers->perPage() . "\n";
echo "Current page: " . $providers->currentPage() . "\n";
echo "Last page: " . $providers->lastPage() . "\n";
echo "Has pages: " . ($providers->hasPages() ? 'YES' : 'NO') . "\n";
echo "Has more pages: " . ($providers->hasMorePages() ? 'YES' : 'NO') . "\n";
echo "On first page: " . ($providers->onFirstPage() ? 'YES' : 'NO') . "\n";
echo "First item: " . $providers->firstItem() . "\n";
echo "Last item: " . $providers->lastItem() . "\n";
echo "Next page URL: " . $providers->nextPageUrl() . "\n";
echo "Prev page URL: " . $providers->previousPageUrl() . "\n";
echo "\n=== RENDERED LINKS ===\n";
$links = $providers->links('components.global-pagination');
echo $links ? $links : "NO LINKS RENDERED\n";
