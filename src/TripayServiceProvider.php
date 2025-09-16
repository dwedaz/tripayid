<?php

namespace Tripay\PPOB;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Tripay\PPOB\Console\Commands\ClearCacheCommand;
use Tripay\PPOB\Console\Commands\SyncCategoriesCommand;
use Tripay\PPOB\Console\Commands\SyncOperatorsCommand;
use Tripay\PPOB\Console\Commands\SyncProductsCommand;
use Tripay\PPOB\Console\Commands\TestConnectionCommand;
use Tripay\PPOB\Http\Middleware\VerifyTripaySignature;
use Tripay\PPOB\Services\BalanceService;
use Tripay\PPOB\Services\PostpaidService;
use Tripay\PPOB\Services\PrepaidService;
use Tripay\PPOB\Services\ServerService;
use Tripay\PPOB\Services\TransactionService;
use Tripay\PPOB\Services\TripayHttpClient;

class TripayServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tripay.php', 'tripay');

        $this->registerServices();
        $this->registerCommands();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishResources();
        $this->loadMigrations();
        $this->registerRoutes();
        $this->registerMiddleware();
        $this->registerBackpackIntegration();

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncCategoriesCommand::class,
                SyncOperatorsCommand::class,
                SyncProductsCommand::class,
                TestConnectionCommand::class,
                ClearCacheCommand::class,
            ]);
        }
    }

    /**
     * Register package services.
     */
    protected function registerServices(): void
    {
        $this->app->singleton(TripayHttpClient::class, function ($app) {
            return new TripayHttpClient($app['config']['tripay']);
        });

        $this->app->singleton(ServerService::class, function ($app) {
            return new ServerService($app[TripayHttpClient::class]);
        });

        $this->app->singleton(BalanceService::class, function ($app) {
            return new BalanceService($app[TripayHttpClient::class]);
        });

        $this->app->singleton(PrepaidService::class, function ($app) {
            return new PrepaidService($app[TripayHttpClient::class]);
        });

        $this->app->singleton(PostpaidService::class, function ($app) {
            return new PostpaidService($app[TripayHttpClient::class]);
        });

        $this->app->singleton(TransactionService::class, function ($app) {
            return new TransactionService($app[TripayHttpClient::class]);
        });

        // Main facade service
        $this->app->singleton('tripay', function ($app) {
            return new TripayManager([
                'server' => $app[ServerService::class],
                'balance' => $app[BalanceService::class],
                'prepaid' => $app[PrepaidService::class],
                'postpaid' => $app[PostpaidService::class],
                'transaction' => $app[TransactionService::class],
            ]);
        });
    }

    /**
     * Register package commands.
     */
    protected function registerCommands(): void
    {
        $this->app->singleton(SyncCategoriesCommand::class);
        $this->app->singleton(SyncProductsCommand::class);
        $this->app->singleton(TestConnectionCommand::class);
        $this->app->singleton(ClearCacheCommand::class);
    }

    /**
     * Publish package resources.
     */
    protected function publishResources(): void
    {
        // Config file
        $this->publishes([
            __DIR__.'/../config/tripay.php' => config_path('tripay.php'),
        ], 'tripay-config');

        // Migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'tripay-migrations');

        // Routes (for webhook endpoint)
        $this->publishes([
            __DIR__.'/Http/routes/api.php' => base_path('routes/tripay.php'),
        ], 'tripay-routes');
    }

    /**
     * Load package migrations.
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        if (config('tripay.callback_url')) {
            Route::middleware('api')
                ->prefix('api/tripay')
                ->group(__DIR__.'/Http/routes/api.php');
        }
    }

    /**
     * Register package middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('tripay.signature', VerifyTripaySignature::class);
    }

    /**
     * Register Backpack integration.
     */
    protected function registerBackpackIntegration(): void
    {
        if (config('tripay.backpack.enabled', true) && class_exists('\Backpack\CRUD\BackpackServiceProvider')) {
            $this->loadBackpackRoutes();
            $this->loadBackpackViews();
            $this->registerBackpackMenu();
        }
    }

    /**
     * Load Backpack routes.
     */
    protected function loadBackpackRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/backpack.php');
    }

    /**
     * Load Backpack views.
     */
    protected function loadBackpackViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tripay');
    }

    /**
     * Register Backpack sidebar menu.
     */
    protected function registerBackpackMenu(): void
    {
        // Add the menu item to Backpack's sidebar automatically when package is installed
        if (config('tripay.backpack.menu.enabled', true)) {
            // Use View Composer to inject menu into Backpack layouts
            view()->composer('backpack.*', function ($view) {
                if (class_exists('\Backpack\CRUD\app\Library\Widget')) {
                    // Add the widget to inject our menu
                    \Backpack\CRUD\app\Library\Widget::add([
                        'type' => 'view',
                        'view' => 'tripay::backpack.menu-script',
                        'stack' => 'after_scripts'
                    ]);
                }
            });
            
            // Also try to auto-add to menu_items.blade.php if it doesn't exist
            $this->autoAddToBackpackMenu();
        }
    }


    /**
     * Automatically add Tripay menu to Backpack's menu_items.blade.php if not present.
     */
    protected function autoAddToBackpackMenu(): void
    {
        $menuFile = resource_path('views/vendor/backpack/ui/inc/menu_items.blade.php');
        
        if (file_exists($menuFile)) {
            $content = file_get_contents($menuFile);
            
            // Check if Tripay menu is not already added
            if (strpos($content, 'Tripay PPOB') === false && strpos($content, 'tripay') === false) {
                $menuContent = '
<x-backpack::menu-dropdown title="Tripay PPOB" icon="la la-money-bill">
    <x-backpack::menu-dropdown-item title="Dashboard" icon="la la-tachometer" :link="backpack_url(\'tripay\')" />
    <x-backpack::menu-dropdown-item title="Categories" icon="la la-tags" :link="backpack_url(\'tripay/categories\')" />
    <x-backpack::menu-dropdown-item title="Operators" icon="la la-signal" :link="backpack_url(\'tripay/operators\')" />
    <x-backpack::menu-dropdown-item title="Products" icon="la la-box" :link="backpack_url(\'tripay/products\')" />
    <x-backpack::menu-dropdown-item title="Transactions" icon="la la-exchange-alt" :link="backpack_url(\'tripay/transactions\')" />
</x-backpack::menu-dropdown>';
                
                file_put_contents($menuFile, $content . $menuContent);
            }
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'tripay',
            TripayHttpClient::class,
            ServerService::class,
            BalanceService::class,
            PrepaidService::class,
            PostpaidService::class,
            TransactionService::class,
        ];
    }
}
