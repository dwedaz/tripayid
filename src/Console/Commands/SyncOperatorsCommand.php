<?php

namespace Tripay\PPOB\Console\Commands;

use Illuminate\Console\Command;
use Tripay\PPOB\Facades\Tripay;
use Tripay\PPOB\Models\Category;
use Tripay\PPOB\Models\Operator;

class SyncOperatorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tripay:sync-operators
                           {--type=all : Sync specific type (prepaid, postpaid, or all)}
                           {--force : Force update existing operators}';

    /**
     * The console command description.
     */
    protected $description = 'Sync product operators from Tripay API to database';

   
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Syncing operators from Tripay API...');

        $type = $this->option('type');
        $force = $this->option('force');
        
        try {
            $synced = 0;
            $updated = 0;
            $errors = 0;

            // Sync prepaid operators
            if ($type === 'all' || $type === 'prepaid') {
                $this->info('📱 Syncing prepaid operators...');
                [$prepaidSynced, $prepaidUpdated, $prepaidErrors] = $this->syncOperatorsForType('prepaid', $force);
                $synced += $prepaidSynced;
                $updated += $prepaidUpdated;
                $errors += $prepaidErrors;
            }

            // Sync postpaid operators  
            if ($type === 'all' || $type === 'postpaid') {
                $this->info('🏠 Syncing postpaid operators...');
                [$postpaidSynced, $postpaidUpdated, $postpaidErrors] = $this->syncOperatorsForType('postpaid', $force);
                $synced += $postpaidSynced;
                $updated += $postpaidUpdated;
                $errors += $postpaidErrors;
            }

            // Display results
            $this->newLine();
            $this->info("✅ Operators sync completed!");
            $this->table(['Metric', 'Count'], [
                ['New operators synced', $synced],
                ['Existing operators updated', $updated],
                ['Errors encountered', $errors],
                ['Total processed', $synced + $updated + $errors]
            ]);

            return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to sync operators: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync operators for a specific type
     */
    protected function syncOperatorsForType(string $type, bool $force): array
    {
        $synced = 0;
        $updated = 0;
        $errors = 0;

        try {
            // Get operators from API based on type
            if ($type === 'prepaid') {
                $response = Tripay::prepaid()->getOperators();
            } else {
                $response = Tripay::postpaid()->getOperators();
            }

            if (!$response->success) {
                $this->error("Failed to fetch {$type} operators: " . $response->message);
                return [0, 0, 1];
            }

            $progressBar = $this->output->createProgressBar(count($response->data));
            $progressBar->start();

            foreach ($response->data as $categoryData) {
                try {
                    // Convert API response to array
                    $data = [
                        'id' => $categoryData->category_id ?? $categoryData->id,
                        'name' => $categoryData->category_name ?? $categoryData->name,
                        'type' => $categoryData->type ?? $type,
                        'status' => $categoryData->status ?? false,
                        'billing_type' => $type,
                        'synced_at' => now(),
                    ];

                    // Check if operator already exists
                    $existing = Operator::where('id', $data['id'])->first();

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