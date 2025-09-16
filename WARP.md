# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Overview

This is a Laravel package for integrating Tripay.id PPOB (Payment Point Online Bank) services. It provides a clean, type-safe API for handling both prepaid (pulsa, data, e-money) and postpaid (bill payments) transactions, with a complete Backpack admin panel.

## Quick Commands

### Development Commands
```bash
# Install dependencies
composer install

# Run tests
composer test
# Or with coverage
composer test-coverage

# Code formatting
composer format

# Static analysis
composer analyse

# Test connection to Tripay API
php artisan tripay:test-connection

# Sync data from API
php artisan tripay:sync-categories
php artisan tripay:sync-products
php artisan tripay:clear-cache
```

### Testing Commands
```bash
# Run all tests with Pest
vendor/bin/pest

# Run specific test file
vendor/bin/pest tests/Feature/TripayFacadeTest.php

# Run with coverage
vendor/bin/pest --coverage

# PHPUnit (alternative)
vendor/bin/phpunit
```

## Architecture Overview

The package follows a service-oriented architecture with these key layers:

### Core Components
```
TripayManager (Facade Entry Point)
    ├── ServerService (Connection/Health)
    ├── BalanceService (Account Balance)
    ├── PrepaidService (Pulsa, Data, E-money)
    ├── PostpaidService (Bill Payments)
    └── TransactionService (History & Status)

Each service uses:
    ├── TripayHttpClient (HTTP requests with caching)
    ├── DTOs (Request/Response data objects)
    └── BaseService (Common functionality)
```

### Data Flow
1. **Facade** → `TripayManager` → Specific Service
2. **Service** → `TripayHttpClient` → Tripay API
3. **Response** → DTO transformation → Type-safe objects
4. **Admin Panel** → Backpack CRUD Controllers → Models

### Directory Structure
- `src/Services/` - Business logic and API communication
- `src/DTO/` - Data Transfer Objects for type safety
- `src/Models/` - Eloquent models for database
- `src/Console/Commands/` - Artisan commands
- `routes/backpack.php` - Admin panel routes
- `config/tripay.php` - Package configuration

## Configuration

### Required Environment Variables
```env
# API Credentials
TRIPAY_MODE=sandbox
TRIPAY_API_KEY=your_api_key_here
TRIPAY_SECRET_PIN=your_secret_pin_here

# Webhook Configuration
TRIPAY_CALLBACK_URL=https://yourapp.com/api/tripay/webhook
TRIPAY_CALLBACK_SECRET=your_callback_secret

# Optional Settings
TRIPAY_CACHE_ENABLED=true
TRIPAY_CACHE_TTL=43200
TRIPAY_LOG_ENABLED=true
TRIPAY_BACKPACK_ENABLED=true
```

### Setup Commands
```bash
# Publish config
php artisan vendor:publish --provider="Tripay\PPOB\TripayServiceProvider" --tag="tripay-config"

# Publish migrations (if using Backpack)
php artisan vendor:publish --provider="Tripay\PPOB\TripayServiceProvider" --tag="tripay-migrations"

# Run migrations
php artisan migrate

# Publish webhook routes (optional)
php artisan vendor:publish --provider="Tripay\PPOB\TripayServiceProvider" --tag="tripay-routes"
```

## Development Workflow

### Code Style
- Uses **PHP CS Fixer** with PSR-12 standards
- Configuration in `.php-cs-fixer.php`
- Run: `composer format`

### Static Analysis
- Uses **PHPStan** at level 5
- Configuration in `phpstan.neon`
- Run: `composer analyse`

### Key Development Patterns

#### Using Services
```php
use Tripay\PPOB\Facades\Tripay;

// Via Facade
$balance = Tripay::getBalance();
$result = Tripay::purchasePrepaid('AX5', '08123456789', 'TRX001', '1234');

// Via Service Injection
public function __construct(
    private PrepaidService $prepaid,
    private PostpaidService $postpaid
) {}
```

#### Adding New Endpoints
1. Add method to appropriate service class
2. Define endpoint in `getEndpoint()` method
3. Create/update DTO classes for request/response
4. Add tests for the new functionality

#### Working with DTOs
All API responses use readonly DTOs with type safety:
```php
$products = Tripay::prepaid()->getProducts();
foreach ($products->data as $product) {
    echo $product->product_id;    // string
    echo $product->price;         // ?float
}
```

## Testing Strategy

### Test Structure
- **Pest** as primary testing framework with **PHPUnit** fallback
- `tests/Unit/` - Unit tests for individual classes
- `tests/Feature/` - Integration tests for API interactions
- Uses **Orchestra Testbench** for Laravel package testing

### Running Tests
```bash
# All tests
vendor/bin/pest

# Specific test suite
vendor/bin/pest tests/Unit
vendor/bin/pest tests/Feature

# With coverage
vendor/bin/pest --coverage
```

### Mock Configuration
Tests use mock API responses instead of real API calls. The `TestCase` class sets up:
- Sandbox mode configuration
- Disabled caching and logging
- Mock API credentials

## Backpack Integration

### Admin Panel Routes
All admin routes are prefixed with `/admin/tripay/`:
- `/admin/tripay` - Dashboard
- `/admin/tripay/categories` - Categories CRUD
- `/admin/tripay/operators` - Operators CRUD
- `/admin/tripay/products` - Products CRUD
- `/admin/tripay/transactions` - Transactions (read-only)

### Manual Menu Setup
Due to compatibility issues, Backpack menu requires manual setup. See `BACKPACK_INTEGRATION.md` for detailed instructions.

## Package Development

### Publishing Changes
The package uses semantic versioning. Key files for releases:
- `CHANGELOG.md` - Document changes
- `composer.json` - Update version
- Tag releases in Git

### Common Tasks

#### Adding New API Endpoint
1. Add method to relevant service in `src/Services/`
2. Create request/response DTOs in `src/DTO/`
3. Add endpoint mapping in service's `getEndpoint()` method
4. Write tests in `tests/`
5. Update documentation

#### Database Changes
1. Create migration in `database/migrations/`
2. Update corresponding model in `src/Models/`
3. Add Backpack CRUD controller if needed
4. Update routes in `routes/backpack.php`

### API Documentation
The Tripay API documentation is the primary reference for endpoint specifications and response formats. Always verify API changes against the official docs.

## Troubleshooting

### Common Issues
- **Connection failures**: Check `TRIPAY_API_KEY` and `TRIPAY_MODE` settings
- **Cache issues**: Run `php artisan tripay:clear-cache`
- **Webhook problems**: Verify `TRIPAY_CALLBACK_SECRET` and URL accessibility
- **Backpack menu not showing**: Follow manual setup in `BACKPACK_INTEGRATION.md`

### Debugging Commands
```bash
# Test API connection
php artisan tripay:test-connection

# Clear package cache
php artisan tripay:clear-cache

# Check package health
php artisan route:list | grep tripay
```