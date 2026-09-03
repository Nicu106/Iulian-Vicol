<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locks this environment down to the brandbook and nothing else.
 *
 * Switched with BRANDBOOK_ONLY in .env. It exists so the design copy can be
 * shown to the client without them wandering into half-finished pages: every
 * other route returns a holding page rather than an error, so a mistyped URL
 * during a meeting looks deliberate instead of broken.
 *
 * Static files under public/ (css, js, the storage symlink) are served by nginx
 * before PHP is reached, so they are unaffected. The one Laravel route the
 * brandbook genuinely needs is the image resizer.
 */
class BrandbookOnly
{
    /** Paths that stay reachable. Everything else is held. */
    private const ALLOW = [
        'brandbook',
        'brandbook/*',
        'catalogo',      // the catalogue page, in design
        'img',        // the /img/{w} resize service — vehicle and customer photos
        'img/*',
        'up',         // the health check
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.brandbook_only')) {
            return $next($request);
        }

        if ($request->is(...self::ALLOW)) {
            return $next($request);
        }

        return response()->view('pages.held', [], 200)
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->header('Cache-Control', 'no-store');
    }
}
