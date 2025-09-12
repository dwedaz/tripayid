<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
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
        'api_key' => env('TRIPAY_API_KEY', 'test_api_key_here'),  // Replace with your actual test API key
        'secret_pin' => env('TRIPAY_SECRET_PIN', '1234'),            // Replace with your actual secret PIN
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

echo "🚀 Testing Tripay PPOB Package\n";
echo "==============================\n";
echo "Mode: " . env('TRIPAY_MODE', 'sandbox') . "\n";
echo "API Key: " . (env('TRIPAY_API_KEY') ? substr(env('TRIPAY_API_KEY'), 0, 10) . '...' : 'NOT SET') . "\n\n";

try {
    echo "1. Testing Server Connection...\n";
    $isConnected = Tripay::testConnection();
    echo "   Connection Status: " . ($isConnected ? "✅ Connected" : "❌ Failed") . "\n\n";
    
    if ($isConnected) {
        echo "2. Testing Server Health Check...\n";
        $serverResponse = Tripay::server()->checkServer();
        echo "   Server Response: " . ($serverResponse->success ? "✅ Success" : "❌ Failed") . "\n";
        echo "   Message: " . $serverResponse->message . "\n\n";
        
        echo "3. Testing Balance Check...\n";
        try {
            $balance = Tripay::getBalance();
            echo "   Balance: " . $balance . "\n\n";
        } catch (Exception $e) {
            echo "   Balance Error: " . $e->getMessage() . "\n\n";
        }
        
        echo "4. Testing Service Access...\n";
        echo "   Server Service: " . (Tripay::server() ? "✅ Available" : "❌ Failed") . "\n";
        echo "   Balance Service: " . (Tripay::balance() ? "✅ Available" : "❌ Failed") . "\n";
        echo "   Prepaid Service: " . (Tripay::prepaid() ? "✅ Available" : "❌ Failed") . "\n";
        echo "   Postpaid Service: " . (Tripay::postpaid() ? "✅ Available" : "❌ Failed") . "\n";
        echo "   Transaction Service: " . (Tripay::transaction() ? "✅ Available" : "❌ Failed") . "\n\n";
        
        echo "5. Testing Prepaid Categories...\n";
        try {
            $categories = Tripay::prepaid()->getCategories();
            echo "   Categories Status: " . ($categories->success ? "✅ Success" : "❌ Failed") . "\n";
            echo "   Total Categories: " . count($categories->data) . "\n";
            if (count($categories->data) > 0) {
                echo "   Sample Categories: ";
                $sampleCategories = array_slice($categories->data, 0, 3);
                foreach ($sampleCategories as $i => $category) {
                    echo ($i > 0 ? ", " : "") . $category->category_name;
                }
                echo "\n";
            }
            echo "\n";
        } catch (Exception $e) {
            echo "   Categories Error: " . $e->getMessage() . "\n\n";
        }
        
        echo "6. Testing Prepaid Operators...\n";
        try {
            $operators = Tripay::prepaid()->getOperators();
            echo "   Operators Status: " . ($operators->success ? "✅ Success" : "❌ Failed") . "\n";
            echo "   Total Operators: " . count($operators->data) . "\n";
            if (count($operators->data) > 0) {
                echo "   Sample Operators: ";
                $sampleOperators = array_slice($operators->data, 0, 3);
                foreach ($sampleOperators as $i => $operator) {
                    echo ($i > 0 ? ", " : "") . $operator->category_name;
                }
                echo "\n";
            }
            echo "\n";
        } catch (Exception $e) {
            echo "   Operators Error: " . $e->getMessage() . "\n\n";
        }
        
        echo "7. Testing Prepaid Products List...\n";
        try {
            $products = Tripay::prepaid()->getProducts();
            echo "   Products Status: " . ($products->success ? "✅ Success" : "❌ Failed") . "\n";
            echo "   Total Products: " . count($products->data) . "\n";
            
            if (count($products->data) > 0) {
                echo "   Sample Products:\n";
                $sampleProducts = array_slice($products->data, 0, 5);
                foreach ($sampleProducts as $product) {
                    echo "     - " . $product->product_name . " (ID: " . $product->product_id . ", Price: Rp " . number_format($product->product_price) . ")\n";
                }
                
                // Test getting products by category if we have categories
                if (isset($categories) && count($categories->data) > 0) {
                    $firstCategory = $categories->data[0]->category_id;
                    echo "\n   Testing products by category (" . $categories->data[0]->category_name . ")...\n";
                    $categoryProducts = Tripay::prepaid()->getProductsByCategory($firstCategory);
                    echo "   Category Products: " . count($categoryProducts->data) . " found\n";
                }
                
                // Test getting products by operator if we have operators
                if (isset($operators) && count($operators->data) > 0) {
                    $firstOperator = $operators->data[0]->category_id;
                    echo "\n   Testing products by operator (" . $operators->data[0]->category_name . ")...\n";
                    $operatorProducts = Tripay::prepaid()->getProductsByOperator($firstOperator);
                    echo "   Operator Products: " . count($operatorProducts->data) . " found\n";
                }
            }
            echo "\n";
        } catch (Exception $e) {
            echo "   Products Error: " . $e->getMessage() . "\n\n";
        }
    }

    
    echo "✅ Tripay Package Test Completed Successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    
    if ($e->getPrevious()) {
        echo "Previous Error: " . $e->getPrevious()->getMessage() . "\n";
    }
}