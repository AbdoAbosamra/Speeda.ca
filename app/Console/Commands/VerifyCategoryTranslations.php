<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class VerifyCategoryTranslations extends Command
{
    protected $signature = 'categories:verify-translations';

    protected $description = 'Verify that category translations are working correctly.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Verifying category translations...');
        $this->newLine();

        $issues = [];
        $total = Category::count();
        $verified = 0;

        Category::chunk(50, function ($categories) use (&$issues, &$verified) {
            foreach ($categories as $category) {
                $categoryIssues = [];

                // Check name translations
                if (empty($category->name)) {
                    $categoryIssues[] = 'Missing English name';
                } else {
                    if (empty($category->name_ar)) {
                        $categoryIssues[] = 'Missing Arabic name translation';
                    }
                    if (empty($category->name_fr)) {
                        $categoryIssues[] = 'Missing French name translation';
                    }
                }

                // Check description translations (optional)
                if (! empty($category->description)) {
                    if (empty($category->description_ar)) {
                        $categoryIssues[] = 'Missing Arabic description translation';
                    }
                    if (empty($category->description_fr)) {
                        $categoryIssues[] = 'Missing French description translation';
                    }
                }

                // Test localized accessors
                $originalLocale = app()->getLocale();

                // Test Arabic
                app()->setLocale('ar');
                $arName = $category->localized_name;
                if (empty($arName)) {
                    $categoryIssues[] = 'localized_name returns empty for Arabic locale';
                }

                // Test French
                app()->setLocale('fr');
                $frName = $category->localized_name;
                if (empty($frName)) {
                    $categoryIssues[] = 'localized_name returns empty for French locale';
                }

                // Test English
                app()->setLocale('en');
                $enName = $category->localized_name;
                if (empty($enName)) {
                    $categoryIssues[] = 'localized_name returns empty for English locale';
                }

                // Restore original locale
                app()->setLocale($originalLocale);

                if (empty($categoryIssues)) {
                    $verified++;
                } else {
                    $issues[] = [
                        'id' => $category->id,
                        'name' => $category->name ?? 'N/A',
                        'issues' => $categoryIssues,
                    ];
                }
            }
        });

        // Display results
        $this->info("Total Categories: {$total}");
        $this->info("Verified: {$verified}");
        $this->info('Issues Found: '.count($issues));

        if (! empty($issues)) {
            $this->newLine();
            $this->warn('Categories with issues:');
            $this->table(
                ['ID', 'Name', 'Issues'],
                array_map(function ($issue) {
                    return [
                        $issue['id'],
                        $issue['name'],
                        implode(', ', $issue['issues']),
                    ];
                }, $issues)
            );
        } else {
            $this->newLine();
            $this->info('✅ All categories have proper translations!');
        }

        // Test language switching
        $this->newLine();
        $this->info('Testing language switching...');
        $sampleCategory = Category::first();

        if ($sampleCategory) {
            $originalLocale = app()->getLocale();
            $sampleData = [];

            // Test English
            app()->setLocale('en');
            $sampleData[] = [
                'en',
                $sampleCategory->localized_name ?? 'N/A',
                substr($sampleCategory->localized_description ?? 'N/A', 0, 50).'...',
            ];

            // Test Arabic
            app()->setLocale('ar');
            $sampleData[] = [
                'ar',
                $sampleCategory->localized_name ?? 'N/A',
                substr($sampleCategory->localized_description ?? 'N/A', 0, 50).'...',
            ];

            // Test French
            app()->setLocale('fr');
            $sampleData[] = [
                'fr',
                $sampleCategory->localized_name ?? 'N/A',
                substr($sampleCategory->localized_description ?? 'N/A', 0, 50).'...',
            ];

            // Reset to original locale
            app()->setLocale($originalLocale);

            $this->table(
                ['Locale', 'Name', 'Description'],
                $sampleData
            );
        }

        return count($issues) > 0 ? 1 : 0;
    }
}
