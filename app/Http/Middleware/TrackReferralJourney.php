<?php

namespace App\Http\Middleware;

use App\Models\Vehicle;
use App\Support\Journey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records the pages a referred visitor sees. Does nothing for anyone without
 * the mc_rv cookie — which is everyone who did not arrive through a
 * recommendation link — and nothing for anything that is not a page.
 */
class TrackReferralJourney
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || ! $request->hasCookie(Journey::COOKIE) || $response->getStatusCode() !== 200) {
            return $response;
        }
        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $path = '/' . ltrim($request->path(), '/');
        if (preg_match('~^/(admin|login|logout|r/|img|storage|build|up|brandbook)(/|$)~', $path)) {
            return $response;
        }

        $vehicleId = $request->route()?->getName() === 'coche'
            ? Vehicle::where('slug', (string) $request->route('slug'))->value('id')
            : null;

        Journey::track($request, $vehicleId ? 'car' : 'page', ['path' => $path, 'vehicle_id' => $vehicleId]);

        return $response;
    }
}
