<?php

namespace App\Console\Commands;

use App\Domain\SEO\Services\SitemapService;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'seo:generate-sitemap';
    protected $description = 'Generate the XML sitemap for the application';

    public function handle(SitemapService $sitemapService)
    {
        $this->info('Generating sitemap...');
        $sitemapService->generate();
        $this->info('Sitemap generated successfully at public/sitemap.xml');
    }
}
