<?php

namespace App\Http\Middleware;

use App\Support\Stats;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Anyone signed in to the panel is the owner or his team. On every request they
 * make, signed in, their device keeps the mc_staff cookie for five years — so it
 * is still not counted after they sign out or the session expires — and their
 * connection is theirs for the next hours (Stats::markStaff), which also takes
 * out whatever it was counted for before they signed in.
 *
 * In the web group, after the response, so the login POST itself already marks.
 */
class MarkStatsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            try {
                Stats::markStaff((string) $request->ip());
            } catch (\Throwable $e) {
                // the statistics never break the panel
            }
            if (! Stats::staffDevice($request)) {
                $response->headers->setCookie(Stats::staffCookie());
            }
        }

        return $response;
    }
}
