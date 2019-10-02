<?php

namespace Ekersten\Resizer;

use Illuminate\Support\ServiceProvider;
use Ekersten\Resizer\Console\Commands\ClearCacheCommand;

class ResizerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'ekersten');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'ekersten');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/resizer.php', 'resizer');

        // Register the service the package provides.
        $this->app->singleton('resizer', function ($app) {
            return new Resizer;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['resizer'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/resizer.php' => config_path('resizer.php'),
        ], 'resizer.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/ekersten'),
        ], 'resizer.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/ekersten'),
        ], 'resizer.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/ekersten'),
        ], 'resizer.views');*/

        // Registering package commands.
        $this->commands([
            ClearCacheCommand::class
        ]);
    }
}
