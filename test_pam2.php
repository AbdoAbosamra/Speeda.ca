<?php
$service = app(App\Services\AdminProviderActivityMonitorService::class);
$request = request();
$providers = $service->paginateProviders(15, $request);
$html = $providers->links('components.global-pagination')->toHtml();
if (strpos($html, 'speeda-pagination') !== false) {
    echo "PAGINATION RENDERED SUCCESSFULLY!\n";
} else {
    echo "PAGINATION NOT FOUND IN HTML!\n";
}
file_put_contents('test_output.html', $html);
