<?php

namespace Tripay\PPOB;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Tripay\PPOB\Http\Middleware\VerifyTripaySignature;
use Tripay\PPOB\Services\TripayHttpClient;
use Tripay\PPOB\Services\ServerService;
use Tripay\PPOB\Services\BalanceService;
use Tripay\PPOB\Services\PrepaidService;
use Tripay\PPOB\Services\PostpaidService;
use Tripay\PPOB\Services\TransactionService;
use Tripay\PPOB\Console\Commands\SyncCategoriesCommand;
use Tripay\PPOB\Console\Commands\SyncProductsCommand;
use Tripay\PPOB\Console\Commands\TestConnectionCommand;
use Tripay\PPOB\Console\Commands\ClearCacheCommand;

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