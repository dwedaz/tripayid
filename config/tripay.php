<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tripay PPOB Mode
    |--------------------------------------------------------------------------
    |
    | This option controls whether you are using sandbox or production mode.
    | In sandbox mode, all transactions are test transactions and no real
    | money will be processed.
    |
    | Available options: 'sandbox', 'production'
    |
    */
    'mode' => env('TRIPAY_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Your Tripay API credentials. You can find these in your Tripay dashboard
    | under Profile > API & Callback section.
    |
    */
    'api_key' => env('TRIPAY_API_KEY'),
    'secret_pin' => env('TRIPAY_SECRET_PIN'),

    /*
    |--------------------------------------------------------------------------
    | Base URIs
    |--------------------------------------------------------------------------
    |
    | The base URIs for Tripay API endpoints. These should not need to be
    | changed unless Tripay updates their API endpoints.
    |
    */
    'sandbox_base_uri' => 'https://tripay.id/api-sandbox/v2',
    'production_base_uri' => 'https://tripay.id/api/v2',

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the HTTP client used to make requests to Tripay API.
    |
    */
    'timeout' => env('TRIPAY_TIMEOUT', 30),
    'retry' => env('TRIPAY_RETRY', 3),
    'retry_delay' => env('TRIPAY_RETRY_DELAY', 1000), // milliseconds

    /*
    |--------------------------------------------------------------------------
    | Callback Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for receiving webhooks from Tripay when transaction
    | status changes occur.
    |
    */
    'callback_url' => env('TRIPAY_CALLBACK_URL'),
    'callback_secret' => env('TRIPAY_CALLBACK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for product lists, categories, and other relatively
    | static data to improve performance.
    |
    */
    'cache' => [
        'enabled' => env('TRIPAY_CACHE_ENABLED', true),
        'ttl' => env('TRIPAY_CACHE_TTL', 43200), // 12 hours in seconds
        'prefix' => env('TRIPAY_CACHE_PREFIX', 'tripay'),
        'store' => env('TRIPAY_CACHE_STORE', null), // null = use default
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for Tripay API requests and responses.
    |
    */
    'logging' => [
        'enabled' => env('TRIPAY_LOG_ENABLED', true),
        'channel' => env('TRIPAY_LOG_CHANNEL', 'default'),
        'level' => env('TRIPAY_LOG_LEVEL', 'info'),
        'requests' => env('TRIPAY_LOG_REQUESTS', false),
        'responses' => env('TRIPAY_LOG_RESPONSES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API requests to respect Tripay's API limits.
    |
    */
    'rate_limiting' => [
        'enabled' => env('TRIPAY_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('TRIPAY_RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('TRIPAY_RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure database table names and settings for storing Tripay data.
    |
    */
    'database' => [
        'tables' => [
            'categories' => 'tripay_categories',
            'operators' => 'tripay_operators',
            'products' => 'tripay_products',
            'transactions' => 'tripay_transactions',
            'webhooks' => 'tripay_webhooks',
        ],
        'connection' => env('TRIPAY_DB_CONNECTION', null), // null = use default
    ],

    /*
    |--------------------------------------------------------------------------
    | Backpack Integration
    |--------------------------------------------------------------------------
    |
    | Configure Backpack admin panel integration
    |
    */
    'backpack' => [
        'enabled' => env('TRIPAY_BACKPACK_ENABLED', true),
        'prefix' => 'admin',
        'middleware' => ['web', 'admin'],
        'menu' => [
            'enabled' => false,
            'position' => 'sidebar-after-user',
            'icon' => 'la la-money-bill',
            'title' => 'Tripay PPOB',
        ],
        'dashboard' => [
            'enabled' => true,
            'widgets' => [
                'balance' => true,
                'recent_transactions' => true,
                'popular_products' => true,
                'system_health' => true,
            ],
        ],
    ],
];