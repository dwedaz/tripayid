# Laravel Tripay PPOB with Backpack Admin

[![Latest Version](https://img.shields.io/packagist/v/dwedaz/tripayid.svg?style=flat-square)](https://packagist.org/packages/dwedaz/tripayid)
[![Total Downloads](https://img.shields.io/packagist/dt/dwedaz/tripayid.svg?style=flat-square)](https://packagist.org/packages/dwedaz/tripayid)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Tests](https://img.shields.io/github/actions/workflow/status/dwedaz/tripayid/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dwedaz/tripayid/actions)
[![Laravel](https://img.shields.io/badge/Laravel-10%2B-red.svg?style=flat-square)](https://laravel.com)
[![Backpack](https://img.shields.io/badge/Backpack-6.0%2B-blue.svg?style=flat-square)](https://backpackforlaravel.com)

A comprehensive Laravel package for integrating **Tripay.id PPOB (Payment Point Online Bank)** services with a beautiful **Backpack admin panel**. This package provides a clean, type-safe API for handling both **prepaid** (pulsa, data, e-money) and **postpaid** (bill payments like PLN, PDAM, TV, etc.) transactions, complete with a professional admin dashboard for managing your PPOB business.

## 🎆 Features

### 📊 Admin Panel (Backpack Integration)
- **🎨 Beautiful Dashboard** - Real-time statistics, balance monitoring, and transaction overview
- **🗂 Categories Management** - CRUD interface for managing product categories
- **🏢 Operators Management** - Manage telecom operators with logos and status
- **📦 Products Catalog** - Complete product management with pricing and profit calculations
- **📈 Transaction Monitor** - View, filter, and analyze all transactions
- **🔄 One-Click Sync** - Synchronize data from Tripay API with one click
- **📱 Responsive Design** - Works perfectly on desktop and mobile devices
- **🔍 Advanced Filtering** - Filter by category, operator, status, date range, and more

### ⚡ Core API Features  
- **✨ Complete API Coverage** - Full integration with Tripay.id PPOB API endpoints
- **🔒 Type Safety** - Strongly typed DTOs with PHP 8.1+ readonly properties
- **⚡ High Performance** - Built-in caching for product catalogs and categories
- **🚀 Laravel Integration** - Service provider, facades, and Artisan commands
- **🎯 Error Handling** - Comprehensive exception handling with detailed error messages
- **📈 Logging** - Configurable request/response logging
- **🔄 Retry Logic** - Automatic retry with exponential backoff
- **🔐 Webhook Security** - HMAC signature verification for callbacks

## Requirements

- PHP 8.1+
- Laravel 10.0+
```

## 🎨 Backpack Admin Panel

### Installation with Backpack

This package includes a beautiful admin panel built with Backpack CRUD. To use the admin interface:

1. Install Backpack (if not already installed):
```bash
composer require backpack/crud
php artisan backpack:install
```

2. Run migrations to create the admin tables:
```bash
php artisan migrate
```

3. Access the admin panel:
- **Dashboard**: `https://yourapp.com/admin/tripay`
- **Categories**: `https://yourapp.com/admin/tripay/categories`
- **Operators**: `https://yourapp.com/admin/tripay/operators`
- **Products**: `https://yourapp.com/admin/tripay/products`
- **Transactions**: `https://yourapp.com/admin/tripay/transactions`

### Admin Features

- 📊 **Real-time Dashboard** - Live balance, transaction counts, and quick actions
- 🗂 **Category Management** - Add, edit, and organize product categories
- 🏢 **Operator Management** - Manage telecom operators with logos and status
- 📦 **Product Catalog** - Complete product management with pricing and profit tracking
- 📈 **Transaction Monitor** - Filter and analyze all transactions with advanced search
- 🔄 **One-Click Sync** - Sync all data from Tripay API with a single button
- 📱 **Responsive Design** - Fully responsive interface for mobile and desktop

![Tripay Admin Dashboard](https://via.placeholder.com/800x400/4f46e5/ffffff?text=Tripay+Admin+Dashboard)

## Installation

Install the package via Composer:
```bash
composer require dwedaz/tripayid
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Tripay\PPOB\TripayServiceProvider" --tag="tripay-config"
```

## Configuration

Add your Tripay API credentials to your `.env` file:

```env
TRIPAY_MODE=sandbox
TRIPAY_API_KEY=your_api_key_here
TRIPAY_SECRET_PIN=your_secret_pin_here
TRIPAY_CALLBACK_URL=https://yourapp.com/api/tripay/webhook
TRIPAY_CALLBACK_SECRET=your_callback_secret

# Optional caching settings
TRIPAY_CACHE_ENABLED=true
TRIPAY_CACHE_TTL=43200

# Optional logging settings  
TRIPAY_LOG_ENABLED=true
TRIPAY_LOG_REQUESTS=false
TRIPAY_LOG_RESPONSES=false
```

## Basic Usage

### Using the Facade

```php
use Tripay\PPOB\Facades\Tripay;

// Test connection
$isConnected = Tripay::testConnection();

// Check balance
$balance = Tripay::getBalance();

// Get prepaid categories
$categories = Tripay::prepaid()->getCategories();

// Purchase prepaid product
$result = Tripay::purchasePrepaid('AX5', '08123456789', 'INV001', '1234');

// Check postpaid bill
$bill = Tripay::checkBill('PLN', '08123456789', '123456789012', '1234', 'INV002');

// Pay postpaid bill
$payment = Tripay::payBill($bill->trxid, 'INV002', '1234');
```

### Using Service Classes

```php
use Tripay\PPOB\Services\PrepaidService;
use Tripay\PPOB\Services\PostpaidService;
use Tripay\PPOB\Services\TransactionService;

public function __construct(
    private PrepaidService $prepaid,
    private PostpaidService $postpaid,
    private TransactionService $transaction
) {}

public function buyPulsa()
{
    // Get available products
    $products = $this->prepaid->getProducts();
    
    // Get products by operator
    $axisProducts = $this->prepaid->getProductsByOperator('AX');
    
    // Search products
    $searchResults = $this->prepaid->searchProducts('5000');
    
    // Purchase
    $result = $this->prepaid->purchase('AX5', '08123456789', 'TRX001', '1234');
    
    return $result;
}

public function payElectricityBill()
{
    // Check bill first
    $bill = $this->postpaid->checkBillByParams(
        'PLN', 
        '08123456789', 
        '123456789012', 
        '1234'
    );
    
    if ($bill->success) {
        // Pay the bill
        $payment = $this->postpaid->payBillByParams(
            $bill->trxid,
            'TRX002', 
            '1234'
        );
        return $payment;
    }
    
    return $bill;
}

public function getTransactionHistory()
{
    // Get all transactions
    $history = $this->transaction->getHistory();
    
    // Get today's transactions  
    $today = $this->transaction->getTodayTransactions();
    
    // Get pending transactions
    $pending = $this->transaction->getPendingTransactions();
    
    // Search transactions
    $search = $this->transaction->searchTransactions('pulsa');
    
    return $history;
}
```

## Available Services

### Server Service
```php
Tripay::server()->checkServer();
Tripay::server()->testConnection();
```

### Balance Service  
```php
Tripay::balance()->getBalance();
Tripay::balance()->getBalanceAmount(); // Returns float
Tripay::balance()->isSufficientBalance(10000);
```

### Prepaid Service (Pulsa, Data, E-money, etc.)
```php
// Categories and Products
Tripay::prepaid()->getCategories();
Tripay::prepaid()->getOperators();
Tripay::prepaid()->getProducts();
Tripay::prepaid()->getProductsByOperator('TSEL');
Tripay::prepaid()->getProductDetail('TSEL5');
Tripay::prepaid()->searchProducts('5000');

// Transactions
Tripay::prepaid()->purchase($productId, $phone, $apiTrxId, $pin);
```

### Postpaid Service (PLN, PDAM, TV bills, etc.)
```php
// Categories and Products
Tripay::postpaid()->getCategories();
Tripay::postpaid()->getOperators();  
Tripay::postpaid()->getProducts();
Tripay::postpaid()->getProductDetail('PLN');

// Bill Operations
Tripay::postpaid()->checkBillByParams($productId, $phone, $customerNo, $pin);
Tripay::postpaid()->payBillByParams($trxId, $apiTrxId, $pin);
Tripay::postpaid()->checkAndPayBill(..., $autoPay = true); // Check and pay in one call
```

### Transaction Service
```php
// History
Tripay::transaction()->getHistory();
Tripay::transaction()->getHistoryByDate('2024-01-01', '2024-01-31');
Tripay::transaction()->getTodayTransactions();
Tripay::transaction()->getThisMonthTransactions();

// Details
Tripay::transaction()->getDetail('TRX001'); // By API transaction ID
Tripay::transaction()->getDetailByTrxId(12345); // By Tripay transaction ID

// Filtering
Tripay::transaction()->getPendingTransactions();
Tripay::transaction()->getSuccessfulTransactions();  
Tripay::transaction()->getFailedTransactions();
Tripay::transaction()->searchTransactions('pulsa');
```

## Data Transfer Objects (DTOs)

This package uses strongly-typed DTOs for all API responses:

```php
// Category Response
$categories = Tripay::prepaid()->getCategories();
foreach ($categories->data as $category) {
    echo $category->product_id;    // string
    echo $category->product_name;  // string
}

// Product Response
$products = Tripay::prepaid()->getProducts();
foreach ($products->data as $product) {
    echo $product->product_id;     // string
    echo $product->product_name;   // string  
    echo $product->price;          // ?float
    echo $product->selling_price;  // ?float
}

// Transaction Response
$result = Tripay::prepaid()->purchase('AX5', '08123456789', 'TRX001', '1234');
echo $result->success;    // bool
echo $result->message;    // string
echo $result->trxid;      // ?int
echo $result->api_trxid;  // ?string
```

## Error Handling

The package provides comprehensive error handling:

```php
use Tripay\PPOB\Exceptions\ApiException;
use Tripay\PPOB\Exceptions\AuthenticationException;
use Tripay\PPOB\Exceptions\ValidationException;

try {
    $result = Tripay::prepaid()->purchase('AX5', '08123456789', 'TRX001', '1234');
} catch (AuthenticationException $e) {
    // Invalid API key or credentials
    Log::error('Auth failed: ' . $e->getMessage());
} catch (ValidationException $e) {
    // Validation errors
    $errors = $e->getErrors();
    Log::error('Validation failed: ', $errors);
} catch (ApiException $e) {
    // General API errors
    Log::error('API Error: ' . $e->getMessage(), $e->getContext());
}
```

## Caching

Products and categories are automatically cached to improve performance:

```php
// Cache configuration in config/tripay.php
'cache' => [
    'enabled' => true,
    'ttl' => 43200, // 12 hours
    'prefix' => 'tripay',
    'store' => null, // Use default cache store
],

// Manual cache control
Tripay::prepaid()->getClient()->clearCache(); // Clear all cache
```

## Artisan Commands

The package includes useful Artisan commands:

```bash
# Test API connection
php artisan tripay:test-connection

# Sync categories from API (useful for seeding)
php artisan tripay:sync-categories

# Sync products from API
php artisan tripay:sync-products

# Clear package cache
php artisan tripay:clear-cache
```

## Webhooks

Handle Tripay callbacks securely:

1. Publish webhook routes:
```bash
php artisan vendor:publish --provider="Tripay\PPOB\TripayServiceProvider" --tag="tripay-routes"
```

2. The package will automatically register webhook endpoint at `/api/tripay/webhook`

3. Create a listener for transaction updates:
```php
use Tripay\PPOB\Events\TripayTransactionUpdated;

class UpdateTransactionStatus
{
    public function handle(TripayTransactionUpdated $event)
    {
        $transactionData = $event->transactionData;
        $signature = $event->signature;
        
        // Update your transaction status
        Transaction::where('api_trx_id', $transactionData['api_trxid'])
                  ->update(['status' => $transactionData['status']]);
    }
}
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security

If you discover any security related issues, please email security@example.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- 📚 [Documentation](docs/)  
- 🐛 [Issue Tracker](https://github.com/dwedaz/tripayid/issues)
- 💬 [Discussions](https://github.com/dwedaz/tripayid/discussions)
