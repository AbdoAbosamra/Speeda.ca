<?php
$providers = App\Models\ServiceProvider::paginate(5);
echo $providers->links('components.global-pagination')->toHtml();
