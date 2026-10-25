<?php

namespace App\Http\Controllers;

use App\Support\Stats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

/**
 * "No contar este dispositivo": the link (signed, from the Estadísticas page) that
 * keeps a phone of the team out of the statistics without signing in to the
 * panel — the colleague's phone, the owner's second browser. Opening it sets the
 * mc_staff cookie (Stats::staffCookie) and marks the connection; the button on
 * the page undoes it for this browser.
 *
 * Harmless if it leaks: all it can do is make someone's own visits not count.
 */
class StatsMeController extends Controller
{
    public function show(Request $request)
    {
        Stats::markStaff((string) $request->ip());

        return response()
            ->view('stats-me', ['on' => true, 'undo' => \URL::signedRoute('stats.me.undo')])
            ->withCookie(Stats::staffCookie())
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store');
    }

    public function undo(Request $request)
    {
        DB::table('stat_staff')->where('ip', Stats::connection((string) $request->ip()))->delete();

        return response()
            ->view('stats-me', ['on' => false, 'again' => \URL::signedRoute('stats.me')])
            ->withCookie(Cookie::forget(Stats::STAFF_COOKIE))
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store');
    }
}
