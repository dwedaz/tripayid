<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!str_contains($line, '=')) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Helper function for env variables
function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

use Illuminate\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Tripay\PPOB\TripayServiceProvider;
use Tripay\PPOB\TripayManager;
use Tripay\PPOB\Facades\Tripay;

// Create a minimal Laravel container for testing
$app = new Container();
$app->singleton('app', function () use ($app) {
    return $app;
});

// Register HTTP Client Factory
$app->singleton(HttpFactory::class, function () {
    return new HttpFactory();
});

// Set up basic configuration
$config = [
    'tripay' => [
        'mode' => env('TRIPAY_MODE', 'sandbox'),
        'api_key' => env('TRIPAY_API_KEY'),
        'secret_pin' => env('TRIPAY_SECRET_PIN'),
        'sandbox_base_uri' => 'https://tripay.id/api-sandbox/v2',
        'production_base_uri' => 'https://tripay.id/api/v2',
        'timeout' => 30,
        'retry' => 3,
        'retry_delay' => 1000,
        'cache' => [
            'enabled' => false,
        ],
        'logging' => [
            'enabled' => false,
        ],
    ]
];

$app->singleton('config', function () use ($config) {
    return new class($config) implements \ArrayAccess {
        private $config;
        
        public function __construct($config) {
            $this->config = $config;
        }
        
        public function get($key, $default = null) {
            return data_get($this->config, $key, $default);
        }
        
        public function set($key, $value) {
            data_set($this->config, $key, $value);
        }
        
        public function offsetExists($offset): bool {
            return isset($this->config[$offset]);
        }
        
        public function offsetGet($offset): mixed {
            return $this->config[$offset] ?? null;
        }
        
        public function offsetSet($offset, $value): void {
            $this->config[$offset] = $value;
        }
        
        public function offsetUnset($offset): void {
            unset($this->config[$offset]);
        }
    };
});

// Register the service provider
$provider = new TripayServiceProvider($app);
$provider->register();

// Set up the Facade
Tripay::setFacadeApplication($app);

echo "🔄 Syncing categories from Tripay API...\n";
echo "=======================================\n";
echo "Mode: " . env('TRIPAY_MODE', 'sandbox') . "\n";
echo "API Key: " . (env('TRIPAY_API_KEY') ? substr(env('TRIPAY_API_KEY'), 0, 10) . '...' : 'NOT SET') . "\n\n";

try {
    // Test connection first
    echo "1. Testing API connection...\n";
    $isConnected = Tripay::testConnection();
    
    if (!$isConnected) {
        echo "❌ API connection failed. Please check your credentials.\n";
        exit(1);
    }
    
    echo "✅ API connection successful!\n\n";
    
    // Sync prepaid categories
    echo "2. Syncing prepaid categories...\n";
    try {
        $prepaidResponse = Tripay::prepaid()->getCategories();
        
        if ($prepaidResponse->success) {
            echo "✅ Prepaid categories fetched successfully\n";
            echo "   Total categories: " . count($prepaidResponse->data) . "\n";
            
            // Display categories
            foreach ($prepaidResponse->data as $i => $category) {
                $categoryId = $category->category_id ?? $category->id ?? 'N/A';
                $categoryName = $category->category_name ?? $category->name ?? 'N/A';
                $status = isset($category->status) ? ($category->status ? 'Active' : 'Inactive') : 'Unknown';
                
                echo "   " . ($i + 1) . ". ID: {$categoryId} - {$categoryName} ({$status})\n";
            }
        } else {
            echo "❌ Failed to fetch prepaid categories: " . $prepaidResponse->message . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error fetching prepaid categories: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Sync postpaid categories
    echo "3. Syncing postpaid categories...\n";
    try {
        $postpaidResponse = Tripay::postpaid()->getCategories();
        
        if ($postpaidResponse->success) {
            echo "✅ Postpaid categories fetched successfully\n";
            echo "   Total categories: " . count($postpaidResponse->data) . "\n";
            
            // Display categories
            foreach ($postpaidResponse->data as $i => $category) {
                $categoryId = $category->category_id ?? $category->id ?? 'N/A';
                $categoryName = $category->category_name ?? $category->name ?? 'N/A';
                $status = isset($category->status) ? ($category->status ? 'Active' : 'Inactive') : 'Unknown';
                
                echo "   " . ($i + 1) . ". ID: {$categoryId} - {$categoryName} ({$status})\n";
            }
        } else {
            echo "❌ Failed to fetch postpaid categories: " . $postpaidResponse->message . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error fetching postpaid categories: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Category sync simulation completed!\n";
    echo "Note: This script only fetches and displays categories from the API.\n";
    echo "To actually sync to database, run 'php artisan tripay:sync-categories' from a Laravel app with this package installed.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    
    if ($e->getPrevious()) {
        echo "Previous Error: " . $e->getPrevious()->getMessage() . "\n";
    }
    
    exit(1);
}