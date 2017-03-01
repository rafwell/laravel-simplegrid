<?php

namespace Rafwell\Simplegrid;

use Illuminate\Support\ServiceProvider;

class SimplegridServiceProvider extends ServiceProvider
{    
    
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'Simplegrid');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'Simplegrid');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/rafwell/simple-grid'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        /*
         * Register the service provider for the dependency.
         */
        $this->app->register('Maatwebsite\Excel\ExcelServiceProvider');
        /*
         * Create aliases for the dependency.
         */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');        
    
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Connection::class];
    }
}
