<?php

namespace App\Console\Commands;

use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     */
    protected $description = 'Generate XML sitemap for the website';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $urls = collect();

        // Static pages
        $staticPages = [
            ['url' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => route('service-providers.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('categories'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => route('about-us'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => route('privacy-policy'), 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['url' => route('terms-of-service'), 'priority' => '0.3', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $urls->push($page);
        }

        // Service Providers
        $this->info('Adding service providers...');
        ServiceProvider::where('is_active', true)
            ->where('is_verified', true)
            ->chunk(100, function ($providers) use ($urls) {
                foreach ($providers as $provider) {
                    $urls->push([
                        'url' => route('service-providers.show', $provider),
                        'lastmod' => $provider->updated_at->toAtomString(),
                        'priority' => '0.7',
                        'changefreq' => 'weekly',
                    ]);
                }
            });

        // Categories
        $this->info('Adding categories...');
        Category::filterGroups()
            ->each(function ($category) use ($urls) {
                $urls->push([
                    'url' => route('service-providers.index', ['category' => $category->slug]),
                    'lastmod' => $category->updated_at?->toAtomString(),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                ]);
            });

        // Locations
        $this->info('Adding locations...');
        Location::where('is_active', true)
            ->each(function ($location) use ($urls) {
                $urls->push([
                    'url' => route('service-providers.index', ['location' => $location->id]),
                    'lastmod' => $location->updated_at?->toAtomString(),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                ]);
            });

        // Generate XML
        $xml = $this->generateXml($urls);

        // Save to public folder
        $path = public_path('sitemap.xml');
        File::put($path, $xml);

        $this->info("Sitemap generated successfully: {$path}");
        $this->info("Total URLs: {$urls->count()}");

        return Command::SUCCESS;
    }

    /**
     * Generate XML from URLs collection.
     */
    protected function generateXml($urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $urlData) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($urlData['url']) . '</loc>' . PHP_EOL;

            if (isset($urlData['lastmod'])) {
                $xml .= '    <lastmod>' . $urlData['lastmod'] . '</lastmod>' . PHP_EOL;
            }

            if (isset($urlData['changefreq'])) {
                $xml .= '    <changefreq>' . $urlData['changefreq'] . '</changefreq>' . PHP_EOL;
            }

            if (isset($urlData['priority'])) {
                $xml .= '    <priority>' . $urlData['priority'] . '</priority>' . PHP_EOL;
            }

            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
