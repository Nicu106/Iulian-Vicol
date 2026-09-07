<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puts the PHP limits back, for public form posts only.
 *
 * bootstrap/app.php opens this application with "ABSOLUTELY NO LIMITS":
 * memory_limit -1, max_execution_time 0, max_input_time -1, max_input_vars
 * 100000, and public/.user.ini sets post_max_size and upload_max_filesize to 0.
 * That exists for a real reason — the heaviest car folder in this library is
 * 359 MB across 128 files, and the admin uploads sets like that in one go.
 *
 * But those settings apply to EVERY request, including an unauthenticated POST
 * from a stranger. memory_limit and max_execution_time are PHP_INI_ALL, so the
 * ini_set calls really do take effect: one public request may allocate all the
 * RAM on the box and run forever. Six of them — pm.max_children is 6 — is the
 * whole site down, from a form anyone can reach.
 *
 * So the limits come back HERE, on the public route only. The admin keeps its
 * unlimited upload; the open door gets a frame.
 */
class PublicFormLimits
{
    /** Twelve photographs at the 12 MB the form allows, plus headroom for the
     *  multipart envelope and the text. nginx caps the connection at 100M; this
     *  refuses earlier, with an answer instead of a dropped request. */
    private const MAX_BODY = 80 * 1024 * 1024;

    public function handle(Request $request, Closure $next): Response
    {
        $length = (int) $request->server('CONTENT_LENGTH', 0);
        if ($length > self::MAX_BODY) {
            return response()->view('pages.too-big', [
                'mb' => (int) round(self::MAX_BODY / 1048576),
            ], 413);
        }

        ini_set('memory_limit', '512M');        // the pool's own value
        ini_set('max_execution_time', '120');   // the pool's own value
        ini_set('max_input_time', '120');
        ini_set('max_input_vars', '200');       // this form posts ~14

        return $next($request);
    }
}
