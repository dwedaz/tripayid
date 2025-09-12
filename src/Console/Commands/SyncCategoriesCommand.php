<?php

namespace Tripay\PPOB\Console\Commands;

use Illuminate\Console\Command;
use Tripay\PPOB\Facades\Tripay;
use Tripay\PPOB\Models\Category;

class SyncCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tripay:sync-categories 
                           {--type=all : Sync specific type (prepaid, postpaid, or all)}
                           {--force : Force update existing categories}';

    /**
     * The console command description.
     */
    protected $description = 'Sync product categories from Tripay API to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Syncing categories from Tripay API...');
        
        $type = $this->option('type');
        $force = $this->option('force');
        
        try {
            $synced = 0;
            $updated = 0;
            $errors = 0;

            // Sync prepaid categories
            if ($type === 'all' || $type === 'prepaid') {
                $this->info('📱 Syncing prepaid categories...');
                [$prepaidSynced, $prepaidUpdated, $prepaidErrors] = $this->syncCategoriesForType('prepaid', $force);
                $synced += $prepaidSynced;
                $updated += $prepaidUpdated;
                $errors += $prepaidErrors;
            }

            // Sync postpaid categories  
            if ($type === 'all' || $type === 'postpaid') {
                $this->info('🏠 Syncing postpaid categories...');
                [$postpaidSynced, $postpaidUpdated, $postpaidErrors] = $this->syncCategoriesForType('postpaid', $force);
                $synced += $postpaidSynced;
                $updated += $postpaidUpdated;
                $errors += $postpaidErrors;
            }

            // Display results
            $this->newLine();
            $this->info("✅ Categories sync completed!");
            $this->table(['Metric', 'Count'], [
                ['New categories synced', $synced],
                ['Existing categories updated', $updated],
                ['Errors encountered', $errors],
                ['Total processed', $synced + $updated + $errors]
            ]);

            return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to sync categories: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync categories for a specific type
     */
    protected function syncCategoriesForType(string $type, bool $force): array
    {
        $synced = 0;
        $updated = 0;
        $errors = 0;

        try {
            // Get categories from API based on type
            if ($type === 'prepaid') {
                $response = Tripay::prepaid()->getCategories();
            } else {
                $response = Tripay::postpaid()->getCategories();
            }

            if (!$response->success) {
                $this->error("Failed to fetch {$type} categories: " . $response->message);
                return [0, 0, 1];
            }

            $progressBar = $this->output->createProgressBar(count($response->data));
            $progressBar->start();

            foreach ($response->data as $categoryData) {
                try {
                    // Convert API response to array
                    $data = [
                        'category_id' => $categoryData->category_id ?? $categoryData->product_id,
                        'category_name' => $categoryData->category_name ?? $categoryData->product_name,
                        'description' => $categoryData->description ?? null,
                        'type' => $type,
                        'status' => true,
                        'synced_at' => now(),
                    ];

                    // Check if category already exists
                    $existing = Category::where('category_id', $data['category_id'])->first();

                    if ($existing) {
                        if ($force) {
                            $existing->update($data);
                            $updated++;
                        }
                        // Skip if not forcing update
                    } else {
                        Category::create($data);
                        $synced++;
                    }

                } catch (\Exception $e) {
                    $this->error("Error processing category: " . $e->getMessage());
                    $errors++;
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

        } catch (\Exception $e) {
            $this->error("Error fetching {$type} categories: " . $e->getMessage());
            $errors++;
        }

        return [$synced, $updated, $errors];
    }
}