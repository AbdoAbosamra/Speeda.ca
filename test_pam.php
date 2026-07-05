<?php
$service = app(App\Services\AdminProviderActivityMonitorService::class);
$request = request();
$providers = $service->paginateProviders(15, $request);
$html = view('admin.provider_activity_monitor.index', compact('providers'))->render();
if (strpos($html, 'speeda-pagination') !== false) {
    echo "PAGINATION RENDERED!\n";
} else {
    echo "PAGINATION NOT FOUND IN HTML!\n";
}
file_put_contents('test_output.html', $html);
