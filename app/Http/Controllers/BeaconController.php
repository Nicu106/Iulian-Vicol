<?php

namespace App\Http\Controllers;

use App\Support\Stats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * POST /s — the browser's beacon for the panel's statistics (App\Support\Stats).
 *
 * Always 204, counted or not: a robot learns nothing from the answer, and a
 * counting fault (a locked database) never shows on the site.
 */
class BeaconController extends Controller
{
    public function hit(Request $request)
    {
        try {
            Stats::hit($request);
        } catch (\Throwable $e) {
            Log::warning('stats beacon: ' . $e->getMessage());
        }

        return response()->noContent();
    }
}
