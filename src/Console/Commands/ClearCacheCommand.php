<?php

namespace Tripay\PPOB\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tripay:clear-cache 
                           {--type=all : Clear specific cache type (products, categories, operators, balance, all)}
                           {--confirm : Skip confirmation prompt}';

    /**
     * The console command description.
     */
    protected $description = 'Clear Tripay package cache data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $confirm = $this->option('confirm');
        
        if (!$confirm) {
            if (!$this->confirm('Are you sure you want to clear Tripay cache?')) {
                $this->info('Cache clearing cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('🧹 Clearing Tripay package cache...');
        $this->newLine();

        $cachePrefix = config('tripay.cache.prefix', 'tripay');
        $cleared = 0;

        try {
            switch ($type) {
                case 'products':
                    $cleared = $this->clearProductsCache($cachePrefix);
                    break;
                    
                case 'categories':
                    $cleared = $this->clearCategoriesCache($cachePrefix);
                    break;
                    
                case 'operators':
                    $cleared = $this->clearOperatorsCache($cachePrefix);
                    break;
                    
                case 'balance':
                    $cleared = $this->clearBalanceCache($cachePrefix);
                    break;
                    
                case 'all':
                default:
                    $cleared = $this->clearAllCache($cachePrefix);
                    break;
            }

            $this->newLine();
            $this->info('✅ Cache clearing completed!');
            $this->table(['Cache Type', 'Status'], [
                ['Cleared Items', $cleared],
                ['Cache Store', config('cache.default')],
                ['Cache Prefix', $cachePrefix],
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to clear cache: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Clear products cache
     */
    protected function clearProductsCache(string $prefix): int
    {
        $this->info('📦 Clearing products cache...');
        
        $patterns = [
            "{$prefix}_prepaid_products_*",
            "{$prefix}_postpaid_products_*",
            "{$prefix}_product_detail_*",
        ];

        return $this->clearCacheByPatterns($patterns);
    }

    /**
     * Clear categories cache
     */
    protected function clearCategoriesCache(string $prefix): int
    {
        $this->info('🗂 Clearing categories cache...');
        
        $patterns = [
            "{$prefix}_prepaid_categories",
            "{$prefix}_postpaid_categories",
        ];

        return $this->clearCacheByPatterns($patterns);
    }

    /**
     * Clear operators cache
     */
    protected function clearOperatorsCache(string $prefix): int
    {
        $this->info('🏢 Clearing operators cache...');
        
        $patterns = [
            "{$prefix}_prepaid_operators",
            "{$prefix}_postpaid_operators",
        ];

        return $this->clearCacheByPatterns($patterns);
    }

    /**
     * Clear balance cache
     */
    protected function clearBalanceCache(string $prefix): int
    {
        $this->info('💰 Clearing balance cache...');
        
        $patterns = [
            "{$prefix}_balance",
            "{$prefix}_balance_*",
        ];

        return $this->clearCacheByPatterns($patterns);
    }

    /**
     * Clear all Tripay cache
     */
    protected function clearAllCache(string $prefix): int
    {
        $this->info('🧹 Clearing all Tripay cache...');
        
        $patterns = [
            "{$prefix}_*",
        ];

        return $this->clearCacheByPatterns($patterns);
    }

    /**
     * Clear cache by patterns
     */
    protected function clearCacheByPatterns(array $patterns): int
    {
        $cleared = 0;

        foreach ($patterns as $pattern) {
            try {
                // For simple cache drivers, we'll try to clear specific keys
                if (method_exists(Cache::getStore(), 'flush')) {
                    // If it's a tagged cache or supports flushing by pattern
                    $keys = $this->getCacheKeys($pattern);
                    foreach ($keys as $key) {
                        if (Cache::forget($key)) {
                            $cleared++;
                        }
                    }
                } else {
                    // Fallback to clearing known cache keys
                    $knownKeys = $this->getKnownCacheKeys($pattern);
                    foreach ($knownKeys as $key) {
                        if (Cache::forget($key)) {
                            $cleared++;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->warn("Warning: Could not clear pattern {$pattern}: " . $e->getMessage());
            }
        }

        return $cleared;
    }

    /**
     * Get cache keys matching pattern (simplified approach)
     */
    protected function getCacheKeys(string $pattern): array
    {
        // This is a simplified implementation
        // In a real-world scenario, you might need to implement 
        // pattern matching based on your cache driver
        
        $keys = [];
        $basePattern = str_replace('*', '', $pattern);
        
        // Try common cache key variations
        $variations = [
            $basePattern,
            $basePattern . 'data',
            $basePattern . 'response',
        ];

        return $variations;
    }

    /**
     * Get known cache keys for pattern
     */
    protected function getKnownCacheKeys(string $pattern): array
    {
        $prefix = config('tripay.cache.prefix', 'tripay');
        
        // Define known cache keys based on the package's caching strategy
        $knownKeys = [
            // Product cache keys
            "{$prefix}_prepaid_products",
            "{$prefix}_postpaid_products",
            "{$prefix}_prepaid_products_TSEL",
            "{$prefix}_prepaid_products_AXIS",
            "{$prefix}_prepaid_products_INDOSAT",
            "{$prefix}_prepaid_products_XL",
            "{$prefix}_prepaid_products_TRI",
            
            // Category cache keys
            "{$prefix}_prepaid_categories",
            "{$prefix}_postpaid_categories",
            
            // Operator cache keys
            "{$prefix}_prepaid_operators",
            "{$prefix}_postpaid_operators",
            
            // Balance cache keys
            "{$prefix}_balance",
            
            // Transaction cache keys
            "{$prefix}_transactions",
        ];

        // Filter keys that match the pattern
        return array_filter($knownKeys, function($key) use ($pattern) {
            return fnmatch($pattern, $key);
        });
    }
}