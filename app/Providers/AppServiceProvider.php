<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\CreateTestVehicle;

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
        // Force PHP settings for file uploads - ABSOLUTELY NO LIMITS
        if (function_exists('ini_set')) {
            // These settings cannot be changed at runtime, but we can try
            // The real solution is in .user.ini and .htaccess
            ini_set('memory_limit', '-1');
            ini_set('max_execution_time', '0');
            ini_set('max_input_time', '-1');
            ini_set('max_file_uploads', '1000');
            ini_set('max_input_vars', '100000');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateTestVehicle::class,
            ]);
        }
    }
}
