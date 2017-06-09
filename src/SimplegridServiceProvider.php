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
            __DIR__.'/../config/rafwell-simplegrid.php' => config_path('rafwell-simplegrid.php'),
        ]);

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
