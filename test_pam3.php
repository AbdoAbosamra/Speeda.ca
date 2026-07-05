<?php
$p = app(App\Services\AdminProviderActivityMonitorService::class)->paginateProviders(15);
var_dump($p->hasPages());
var_dump($p->total());
