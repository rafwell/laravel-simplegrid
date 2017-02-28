<?php

namespace Rafwell\Easygrid;

use Illuminate\Support\ServiceProvider;

class EasygridServiceProvider extends ServiceProvider
{    
    
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'grid');
        $this->publishes([
            __DIR__.'/public' => public_path('vendor/rafwell/easy-grid'),
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
