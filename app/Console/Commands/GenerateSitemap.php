<?php

namespace App\Console\Commands;

use App\Domain\SEO\Services\SitemapService;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'seo:generate-sitemap
                            {--url= : Override the base URL (defaults to APP_URL from .env)}';
    protected $description = 'Generate the XML sitemap for the application';

    public function handle(SitemapService $sitemapService)
    {
        $baseUrl = $this->option('url') ?: config('app.url');

        // Warn loudly if the resolved base URL still points at localhost so that
        // a developer notices the problem before the file reaches production.
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $this->warn("⚠  Base URL is \"{$baseUrl}\" — sitemap will contain localhost URLs!");
            $this->warn('   Pass --url=https://speeda.ca or set APP_URL in .env to fix.');
        }

        $this->info("Generating sitemap with base URL: {$baseUrl}");

        $count = $sitemapService->generate($this->option('url'));

        $this->info("✅ Sitemap generated successfully at public/sitemap.xml ({$count} URLs)");
    }
}
