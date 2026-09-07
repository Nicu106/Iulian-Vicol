<?php

namespace App\Providers;

use App\Support\VersionedUrlGenerator;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\CreateTestVehicle;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Versionado automático de assets: sustituye el generador de URLs por uno
        // que añade ?v=<filemtime>. Nadie tiene que acordarse de usar @assetv.
        $this->app->extend('url', function ($original, $app) {
            $routes = $app['router']->getRoutes();

            $url = new VersionedUrlGenerator(
                $routes,
                $app->rebinding('request', function ($app, $request) {
                    $app['url']->setRequest($request);
                }),
                $app['config']['app.asset_url']
            );

            $url->setRequest($app['request']);
            $url->setSessionResolver(fn () => $app['session'] ?? null);
            $url->setKeyResolver(fn () => $app->make('config')->get('app.key'));

            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // One IP may offer four cars an hour and ten a day. A person selling a car
        // does it once; the second and third are a family's cars. Anything past
        // that is a script, and it gets the same page with an explanation rather
        // than a wall of text about rate limits.
        \Illuminate\Support\Facades\RateLimiter::for('sell-car', function (\Illuminate\Http\Request $request) {
            return [
                \Illuminate\Cache\RateLimiting\Limit::perHour(4)->by($request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perDay(10)->by($request->ip()),
            ];
        });

        // Photographs get their resized versions built as soon as they are saved,
        // not when the first visitor asks for them. See App\Observers\VehicleObserver.
        \App\Models\Vehicle::observe(\App\Observers\VehicleObserver::class);

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
