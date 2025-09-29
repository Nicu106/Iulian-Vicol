<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force PHP settings for file uploads
        if (function_exists('ini_set')) {
            // These settings cannot be changed at runtime, but we can try
            // The real solution is in .user.ini and .htaccess
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '0');
            ini_set('max_input_time', '-1');
        }
    }
}
