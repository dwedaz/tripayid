<?php

namespace Tripay\PPOB;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Tripay\PPOB\Console\Commands\ClearCacheCommand;
use Tripay\PPOB\Console\Commands\SyncCategoriesCommand;
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
            // Use Laravel's View composer to inject menu
            $this->app->booted(function () {
                // Check if we're in a Backpack context
                if (class_exists('\Backpack\CRUD\app\Library\Widget')) {
                    // Add widget to inject our menu
                    \Backpack\CRUD\app\Library\Widget::add([
                        'type' => 'script',
                        'content' => $this->generateMenuScript()
                    ]);
                }
            });
        }
    }

    /**
     * Generate the JavaScript to inject Tripay menu.
     */
    protected function generateMenuScript(): string
    {
        $menuTitle = config('tripay.backpack.menu.title', 'Tripay PPOB');
        $menuIcon = config('tripay.backpack.menu.icon', 'la la-money-bill');
        
        return '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Only add menu if not already present
            if (document.querySelector("[data-tripay-menu]")) return;
            
            // Find the sidebar nav
            const nav = document.querySelector(".sidebar nav ul, .sidebar-menu, .nav.nav-pills.flex-column, .main-sidebar .nav");
            if (!nav) return;
            
            // Create menu HTML
            const menuItem = document.createElement("li");
            menuItem.className = "nav-item nav-dropdown";
            menuItem.setAttribute("data-tripay-menu", "true");
            
            menuItem.innerHTML = `
                <a class="nav-link nav-dropdown-toggle" href="#" onclick="event.preventDefault(); this.parentElement.classList.toggle(\"open\");">
                    <i class="nav-icon ' . $menuIcon . '"></i>
                    ' . $menuTitle . '
                    <i class="nav-arrow fas fa-angle-left" style="float: right; transition: transform 0.3s;"></i>
                </a>
                <ul class="nav-dropdown-items" style="display: none; padding-left: 1rem;">
                    <li class="nav-item">
                        <a class="nav-link" href="' . url(config('backpack.base.route_prefix', '') . '/tripay') . '">
                            <i class="nav-icon la la-tachometer"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="' . url(config('backpack.base.route_prefix', '') . '/tripay/categories') . '">
                            <i class="nav-icon la la-tags"></i>
                            Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="' . url(config('backpack.base.route_prefix', '') . '/tripay/operators') . '">
                            <i class="nav-icon la la-signal"></i>
                            Operators
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="' . url(config('backpack.base.route_prefix', '') . '/tripay/products') . '">
                            <i class="nav-icon la la-box"></i>
                            Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="' . url(config('backpack.base.route_prefix', '') . '/tripay/transactions') . '">
                            <i class="nav-icon la la-exchange-alt"></i>
                            Transactions
                        </a>
                    </li>
                </ul>
            `;
            
            // Add click handler for dropdown
            const toggle = menuItem.querySelector(".nav-dropdown-toggle");
            toggle.addEventListener("click", function(e) {
                e.preventDefault();
                const parent = this.parentElement;
                const items = parent.querySelector(".nav-dropdown-items");
                const arrow = this.querySelector(".nav-arrow");
                
                if (parent.classList.contains("open")) {
                    parent.classList.remove("open");
                    items.style.display = "none";
                    if (arrow) arrow.style.transform = "rotate(0deg)";
                } else {
                    parent.classList.add("open");
                    items.style.display = "block";
                    if (arrow) arrow.style.transform = "rotate(-90deg)";
                }
            });
            
            // Insert the menu item
            nav.appendChild(menuItem);
        });
        </script>
        <style>
        [data-tripay-menu] .nav-dropdown-items {
            background: rgba(0,0,0,0.1);
            margin: 0.25rem 0;
            border-radius: 0.25rem;
        }
        [data-tripay-menu] .nav-dropdown-items .nav-link {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        </style>';
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
