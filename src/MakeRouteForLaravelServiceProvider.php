<?php

namespace Intellow\MakeRouteForLaravel;

use Illuminate\Support\ServiceProvider;
use Intellow\MakeRouteForLaravel\Console\MakeModelRoute;
use Intellow\MakeRouteForLaravel\Console\MakeRoute;

class MakeRouteForLaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'make-route-for-laravel');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'make-route-for-laravel');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeRoute::class,
                MakeModelRoute::class,
            ]);
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('make-route-for-laravel.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/make-route-for-laravel'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/make-route-for-laravel'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/make-route-for-laravel'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/make-route.php', 'make-route-for-laravel');

        // Register the main class to use with the facade
        $this->app->singleton('make-route-for-laravel', function () {
            return new MakeRouteForLaravel;
        });
    }
}
