<?php

// Force PHP settings for file uploads - ABSOLUTELY NO LIMITS
ini_set('upload_max_filesize', '0');
ini_set('post_max_size', '0');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '0');
ini_set('max_input_time', '-1');
ini_set('max_file_uploads', '1000');
ini_set('max_input_vars', '100000');

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Design-copy lockdown: serve the brandbook and nothing else while the
        // rest of the site is unfinished. Toggle with BRANDBOOK_ONLY in .env.
        $middleware->append(\App\Http\Middleware\BrandbookOnly::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
