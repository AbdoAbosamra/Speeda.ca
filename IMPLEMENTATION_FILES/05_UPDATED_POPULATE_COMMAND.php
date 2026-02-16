<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Updated Populate Category Translations Command
 *
 * CHANGES:
 * 1. Removed dependency on dangerous dictionary
 * 2. Uses only Google Translate API (or requires manual entry)
 * 3. Better error handling
 * 4. Clearer messaging about missing API
 */
class PopulateCategoryTranslations extends Command
{
    protected $signature = 'categories:populate-translations 
                            {--dry-run : Run without making changes}
                            {--force : Overwrite existing translations}
                            {--skip-api-check : Skip Google API check (will fail if API not configured)}';

    protected $description = 'Safely populate missing Arabic and French translations for all categories using Google Translate API.';

    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $skipApiCheck = $this->option('skip-api-check');

        // Check if Google Translate API is configured
        if (! $skipApiCheck && ! $this->translationService->isGoogleTranslateConfigured()) {
            $this->error('❌ Google Translate API is not configured!');
            $this->warn('');
            $this->warn('To use this command, you must:');
            $this->warn('1. Get a Google Translate API key');
            $this->warn('2. Add GOOGLE_TRANSLATE_API_KEY to your .env file');
            $this->warn('3. Configure it in config/services.php');
            $this->warn('');
            $this->warn('Alternatively, manually enter translations in the admin panel.');
            $this->warn('');
            $this->warn('To skip this check (will fail if API not configured), use --skip-api-check');

            return 1;
        }

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting population of missing category translations...');
        $this->info('Source: English (name, description) → Target: Arabic & French');
        $this->info('Using: Google Translate API');
        $this->newLine();

        $stats = [
            'total' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'api_failures' => 0,
            'fields_updated' => [
                'name_ar' => 0,
                'name_fr' => 0,
                'description_ar' => 0,
                'description_fr' => 0,
            ],
        ];

        $errors = [];

        // Process in chunks for memory efficiency
        Category::query()
            ->chunk(50, function ($categories) use (&$stats, &$errors, $dryRun, $force) {
                DB::transaction(function () use ($categories, &$stats, &$errors, $dryRun, $force) {
                    foreach ($categories as $category) {
                        $stats['total']++;
                        $updated = false;
                        $log = [];

                        try {
                            // Determine source text (prefer English, fallback to original name)
                            $sourceName = ! empty($category->name_en) ? $category->name_en : $category->name;
                            $sourceDescription = ! empty($category->description_en) ? $category->description_en : $category->description;

                            if (empty($sourceName)) {
                                $this->warn("Category ID {$category->id} has no English name - skipping");
                                $stats['skipped']++;

                                continue;
                            }

                            // Translate name to Arabic
                            $needsArabicName = ($force || empty($category->name_ar) || $category->name_ar === $category->name);
                            if ($needsArabicName && ! empty($sourceName)) {
                                $translated = $this->translationService->translate($sourceName, 'ar');

                                if ($translated && $translated !== $sourceName) {
                                    if (! $dryRun) {
                                        $category->name_ar = $translated;
                                    }
                                    $log[] = 'name_ar';
                                    $stats['fields_updated']['name_ar']++;
                                    $updated = true;
                                } elseif ($translated === null) {
                                    $stats['api_failures']++;
                                    $this->warn('  ⚠️  Failed to translate name to Arabic (API error or not configured)');
                                }
                            }

                            // Translate name to French
                            $needsFrenchName = ($force || empty($category->name_fr) || $category->name_fr === $category->name);
                            if ($needsFrenchName && ! empty($sourceName)) {
                                $translated = $this->translationService->translate($sourceName, 'fr');

                                if ($translated && $translated !== $sourceName) {
                                    if (! $dryRun) {
                                        $category->name_fr = $translated;
                                    }
                                    $log[] = 'name_fr';
                                    $stats['fields_updated']['name_fr']++;
                                    $updated = true;
                                } elseif ($translated === null) {
                                    $stats['api_failures']++;
                                    $this->warn('  ⚠️  Failed to translate name to French (API error or not configured)');
                                }
                            }

                            // Translate description to Arabic (if source exists)
                            if (! empty($sourceDescription)) {
                                $needsArabicDesc = ($force || empty($category->description_ar) || $category->description_ar === $category->description);
                                if ($needsArabicDesc) {
                                    $translated = $this->translationService->translate($sourceDescription, 'ar');

                                    if ($translated && $translated !== $sourceDescription) {
                                        if (! $dryRun) {
                                            $category->description_ar = $translated;
                                        }
                                        $log[] = 'description_ar';
                                        $stats['fields_updated']['description_ar']++;
                                        $updated = true;
                                    } elseif ($translated === null) {
                                        $stats['api_failures']++;
                                    }
                                }
                            }

                            // Translate description to French (if source exists)
                            if (! empty($sourceDescription)) {
                                $needsFrenchDesc = ($force || empty($category->description_fr) || $category->description_fr === $category->description);
                                if ($needsFrenchDesc) {
                                    $translated = $this->translationService->translate($sourceDescription, 'fr');

                                    if ($translated && $translated !== $sourceDescription) {
                                        if (! $dryRun) {
                                            $category->description_fr = $translated;
                                        }
                                        $log[] = 'description_fr';
                                        $stats['fields_updated']['description_fr']++;
                                        $updated = true;
                                    } elseif ($translated === null) {
                                        $stats['api_failures']++;
                                    }
                                }
                            }

                            if ($updated) {
                                if (! $dryRun) {
                                    $category->save();
                                }
                                $stats['updated']++;
                                $this->line(
                                    ($dryRun ? '[DRY RUN] ' : '').
                                    "✅ Category ID {$category->id} ({$category->name}): ".implode(', ', $log)
                                );
                            } else {
                                $stats['skipped']++;
                            }
                        } catch (\Exception $e) {
                            $stats['errors']++;
                            $errors[] = [
                                'id' => $category->id,
                                'name' => $category->name ?? 'N/A',
                                'error' => $e->getMessage(),
                            ];
                            $this->error("❌ Failed to update category ID {$category->id}: {$e->getMessage()}");
                            Log::error('Category translation error', [
                                'category_id' => $category->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }
                });
            });

        // Display summary
        $this->newLine();
        $this->info('=== Translation Population Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Categories', $stats['total']],
                ['Updated', $stats['updated']],
                ['Skipped (already translated)', $stats['skipped']],
                ['API Failures', $stats['api_failures']],
                ['Errors', $stats['errors']],
            ]
        );

        $this->info('Fields Updated:');
        $this->table(
            ['Field', 'Count'],
            [
                ['name_ar (Arabic name)', $stats['fields_updated']['name_ar']],
                ['name_fr (French name)', $stats['fields_updated']['name_fr']],
                ['description_ar (Arabic description)', $stats['fields_updated']['description_ar']],
                ['description_fr (French description)', $stats['fields_updated']['description_fr']],
            ]
        );

        if ($stats['api_failures'] > 0) {
            $this->warn('');
            $this->warn("⚠️  {$stats['api_failures']} translation(s) failed due to API errors.");
            $this->warn('   These categories will need manual translation in the admin panel.');
        }

        if (! empty($errors)) {
            $this->warn('Errors occurred:');
            foreach ($errors as $err) {
                $this->warn("Category ID {$err['id']} ({$err['name']}): {$err['error']}");
            }
        }

        if ($dryRun) {
            $this->warn('This was a DRY RUN - No changes were made. Run without --dry-run to apply changes.');
        } else {
            $this->info('✅ Translation population complete!');
        }

        return $stats['errors'] > 0 ? 1 : 0;
    }
}
