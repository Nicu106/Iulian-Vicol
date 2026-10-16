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

        // The recommendation cookie holds a random code and nothing else, and the
        // script that adds it to WhatsApp links has to read it.
        $middleware->encryptCookies(except: ['mc_ref']);

        // The pages a referred visitor sees (only those with the mc_rv cookie).
        $middleware->web(append: [\App\Http\Middleware\TrackReferralJourney::class]);

        // navigator.sendBeacon cannot carry a CSRF token. The endpoint only
        // appends an event for the visitor in the requester's own cookie.
        $middleware->validateCsrfTokens(except: ['r/e']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /* "419 Page Expired" in the panel. The owner fills the car form on his phone,
           with breaks; a session that ran out under him ended on a white error page
           with the work gone. The page now keeps the session alive (GET /admin/ping)
           and keeps a copy of the form on the phone, so this is the last line: back
           to the form with what he typed if he is still signed in, otherwise to the
           sign-in, which returns to the form. Public pages keep the standard answer.
           Laravel has already turned the TokenMismatchException into a 419 here. */
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() !== 419 || ! $request->is('admin', 'admin/*')) {
                return null;
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => 'La sesión ha caducado.', 'csrf' => csrf_token(), 'auth' => auth()->check()], 419);
            }
            $back = (string) $request->headers->get('referer');
            $same = $back !== '' && parse_url($back, PHP_URL_HOST) === $request->getHost()
                && str_starts_with((string) parse_url($back, PHP_URL_PATH), '/admin');
            $back = $same ? $back : route('admin.vehicles.index');
            if (auth()->check()) {
                return redirect()->to($back)->withInput($request->except(['_token', 'password']))
                    ->with('error', 'No se ha guardado: la página llevaba demasiado tiempo abierta. Lo que escribiste sigue aquí; pulsa «Guardar» otra vez.');
            }
            $request->session()->put('url.intended', $back);

            return redirect()->route('login')
                ->with('status', 'Tu sesión había caducado y no se ha guardado. Entra otra vez y vuelves al formulario: lo que escribiste sigue guardado en este teléfono.');
        });
    })->create();
