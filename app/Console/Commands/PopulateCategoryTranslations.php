<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PopulateCategoryTranslations extends Command
{
    protected $signature = 'categories:populate-translations 
                            {--dry-run : Run without making changes}
                            {--force : Overwrite existing translations}';

    protected $description = 'Safely populate missing Arabic and French translations for all categories.';

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

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting population of missing category translations...');
        $this->info('Source: English (name, description) → Target: Arabic & French');

        $stats = [
            'total' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
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
                            // Translate name to Arabic
                            // Check if field is empty OR contains same text as English (incorrect translation)
                            $needsArabicName = ($force || empty($category->name_ar) || $category->name_ar === $category->name);
                            if ($needsArabicName && ! empty($category->name)) {
                                $translated = $this->translationService->translate($category->name, 'ar');
                                if ($translated && $translated !== $category->name) {
                                    if (! $dryRun) {
                                        $category->name_ar = $translated;
                                    }
                                    $log[] = 'name_ar';
                                    $stats['fields_updated']['name_ar']++;
                                    $updated = true;
                                }
                            }

                            // Translate name to French
                            // Check if field is empty OR contains same text as English (incorrect translation)
                            $needsFrenchName = ($force || empty($category->name_fr) || $category->name_fr === $category->name);
                            if ($needsFrenchName && ! empty($category->name)) {
                                $translated = $this->translationService->translate($category->name, 'fr');
                                if ($translated && $translated !== $category->name) {
                                    if (! $dryRun) {
                                        $category->name_fr = $translated;
                                    }
                                    $log[] = 'name_fr';
                                    $stats['fields_updated']['name_fr']++;
                                    $updated = true;
                                }
                            }

                            // Translate description to Arabic
                            // Check if field is empty OR contains same text as English (incorrect translation)
                            $needsArabicDesc = ($force || empty($category->description_ar) || $category->description_ar === $category->description);
                            if ($needsArabicDesc && ! empty($category->description)) {
                                $translated = $this->translationService->translate($category->description, 'ar');
                                if ($translated && $translated !== $category->description) {
                                    if (! $dryRun) {
                                        $category->description_ar = $translated;
                                    }
                                    $log[] = 'description_ar';
                                    $stats['fields_updated']['description_ar']++;
                                    $updated = true;
                                }
                            }

                            // Translate description to French
                            // Check if field is empty OR contains same text as English (incorrect translation)
                            $needsFrenchDesc = ($force || empty($category->description_fr) || $category->description_fr === $category->description);
                            if ($needsFrenchDesc && ! empty($category->description)) {
                                $translated = $this->translationService->translate($category->description, 'fr');
                                if ($translated && $translated !== $category->description) {
                                    if (! $dryRun) {
                                        $category->description_fr = $translated;
                                    }
                                    $log[] = 'description_fr';
                                    $stats['fields_updated']['description_fr']++;
                                    $updated = true;
                                }
                            }

                            if ($updated) {
                                if (! $dryRun) {
                                    $category->save();
                                }
                                $stats['updated']++;
                                $this->line(
                                    ($dryRun ? '[DRY RUN] ' : '').
                                    "Category ID {$category->id} ({$category->name}): ".implode(', ', $log)
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
                            $this->error("Failed to update category ID {$category->id}: {$e->getMessage()}");
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
