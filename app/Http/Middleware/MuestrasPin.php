<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * /muestras/* is shown only after the 4-digit PIN (config/muestras.php) has
 * been typed once in this browser session; until then a black page asks for
 * it. Without a PIN configured, nothing is shown at all.
 */
class MuestrasPin
{
    public function handle(Request $request, Closure $next): Response
    {
        // our own test browsers send the PIN as a header (setExtraHTTPHeaders)
        // instead of typing it; it is never read from the URL
        $pin = (string) config('muestras.pin');
        if ($pin !== '' && hash_equals($pin, (string) $request->header('X-Muestras-Pin'))) {
            return $next($request);
        }
        if ($request->session()->get('muestras_ok') === true) {
            return $next($request);
        }

        return response()
            ->view('muestras.pin', ['back' => $request->getRequestUri(), 'wrong' => (bool) $request->session()->pull('muestras_wrong')], 401)
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store');
    }

    /** POST /muestras/pin */
    public static function check(Request $request)
    {
        $pin = (string) config('muestras.pin');
        $back = (string) $request->input('back', '/muestras/por-que');
        if (! str_starts_with($back, '/muestras')) {
            $back = '/muestras/por-que';
        }

        if ($pin !== '' && hash_equals($pin, (string) $request->input('pin'))) {
            $request->session()->regenerate();
            $request->session()->put('muestras_ok', true);
        } else {
            $request->session()->flash('muestras_wrong', true);
        }

        return redirect($back);
    }
}
