<?php

namespace Tripay\PPOB\Console\Commands;

use Illuminate\Console\Command;
use Tripay\PPOB\Facades\Tripay;
use Tripay\PPOB\Models\Product;
use Tripay\PPOB\Models\Category;
use Tripay\PPOB\Models\Operator;

class SyncProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tripay:sync-products 
                           {--type=all : Sync specific type (prepaid, postpaid, or all)}
                           {--force : Force update existing products}
                           {--limit=500 : Maximum number of products to sync per batch}';

    /**
     * The console command description.
     */
    protected $description = 'Sync products from Tripay API to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Syncing products from Tripay API...');
        
        $type = $this->option('type');
        $force = $this->option('force');
        $limit = (int) $this->option('limit');
        
        try {
            $synced = 0;
            $updated = 0;
            $errors = 0;

            // Sync prepaid products
            if ($type === 'all' || $type === 'prepaid') {
                $this->info('📱 Syncing prepaid products...');
                [$prepaidSynced, $prepaidUpdated, $prepaidErrors] = $this->syncProductsForType('prepaid', $force, $limit);
                $synced += $prepaidSynced;
                $updated += $prepaidUpdated;
                $errors += $prepaidErrors;
            }

            // Sync postpaid products  
            if ($type === 'all' || $type === 'postpaid') {
                $this->info('🏠 Syncing postpaid products...');
                [$postpaidSynced, $postpaidUpdated, $postpaidErrors] = $this->syncProductsForType('postpaid', $force, $limit);
                $synced += $postpaidSynced;
                $updated += $postpaidUpdated;
                $errors += $postpaidErrors;
            }

            // Display results
            $this->newLine();
            $this->info("✅ Products sync completed!");
            $this->table(['Metric', 'Count'], [
                ['New products synced', $synced],
                ['Existing products updated', $updated],
                ['Errors encountered', $errors],
                ['Total processed', $synced + $updated + $errors]
            ]);

            return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to sync products: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync products for a specific type
     */
    protected function syncProductsForType(string $type, bool $force, int $limit): array
    {
        $synced = 0;
        $updated = 0;
        $errors = 0;

        try {
            // Get products from API based on type
            if ($type === 'prepaid') {
                $response = Tripay::prepaid()->getProducts();
            } else {
                $response = Tripay::postpaid()->getProducts();
            }

            if (!$response->success) {
                $this->error("Failed to fetch {$type} products: " . $response->message);
                return [0, 0, 1];
            }

            $products = collect($response->data)->take($limit);
            $progressBar = $this->output->createProgressBar($products->count());
            $progressBar->start();

            foreach ($products as $productData) {
                try {
                    // Find or create related category and operator
                    $categoryId = $this->ensureCategoryExists($productData, $type);
                    $operatorId = $this->ensureOperatorExists($productData, $type);

                    // Convert API response to array
                    $data = [
                        'product_id' => $productData->product_id,
                        'product_name' => $productData->product_name,
                        'category_id' => $categoryId,
                        'operator_id' => $operatorId,
                        'product_price' => $productData->product_price ?? $productData->price ?? 0,
                        'selling_price' => $productData->selling_price ?? $productData->product_price ?? $productData->price ?? 0,
                        'description' => $productData->description ?? null,
                        'type' => $type,
                        'status' => $productData->status ?? true,
                        'denomination' => $productData->denomination ?? null,
                        'additional_info' => isset($productData->additional_info) ? $productData->additional_info : null,
                        'cut_off_start' => $productData->cut_off_start ?? null,
                        'cut_off_end' => $productData->cut_off_end ?? null,
                        'synced_at' => now(),
                    ];

                    // Calculate profit margin
                    if ($data['selling_price'] && $data['product_price']) {
                        $data['profit_margin'] = $data['selling_price'] - $data['product_price'];
                    }

                    // Check if product already exists
                    $existing = Product::where('product_id', $data['product_id'])->first();

                    if ($existing) {
                        if ($force) {
                            $existing->update($data);
                            $updated++;
                        }
                        // Skip if not forcing update
                    } else {
                        Product::create($data);
                        $synced++;
                    }

                } catch (\Exception $e) {
                    $this->error("Error processing product: " . $e->getMessage());
                    $errors++;
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

        } catch (\Exception $e) {
            $this->error("Error fetching {$type} products: " . $e->getMessage());
            $errors++;
        }

        return [$synced, $updated, $errors];
    }

    /**
     * Ensure category exists, create if not found
     */
    protected function ensureCategoryExists($productData, string $type): string
    {
        $categoryId = $productData->category ?? 'DEFAULT_' . strtoupper($type);
        $categoryName = $productData->category_name ?? ucfirst($type) . ' Products';
        
        $category = Category::firstOrCreate(
            ['category_id' => $categoryId],
            [
                'category_name' => $categoryName,
                'type' => $type,
                'status' => true,
                'synced_at' => now(),
            ]
        );

        return $category->category_id;
    }

    /**
     * Ensure operator exists, create if not found
     */
    protected function ensureOperatorExists($productData, string $type): string
    {
        $operatorId = $productData->operator ?? 'DEFAULT_' . strtoupper($type);
        $operatorName = $productData->operator_name ?? ucfirst($type) . ' Operator';
        
        $operator = Operator::firstOrCreate(
            ['operator_id' => $operatorId],
            [
                'operator_name' => $operatorName,
                'operator_code' => substr($operatorId, 0, 10),
                'type' => $type,
                'status' => true,
                'synced_at' => now(),
            ]
        );

        return $operator->operator_id;
    }
}