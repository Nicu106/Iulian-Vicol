<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Support\Stats;
use Illuminate\Http\Request;

/**
 * Estadísticas: what people do on the site (App\Support\Stats).
 *
 *   /admin/estadisticas?d=7|30|90           the whole site
 *   /admin/estadisticas?coche={slug}&d=30   one car
 *   …&frag=1                                the same content without the panel
 *                                           around it, for the Panel's popup
 */
class StatsController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->query('d', 30);
        $days = in_array($days, Stats::PERIODS, true) ? $days : 30;
        $frag = $request->boolean('frag');
        $slug = $request->query('coche');

        if (is_string($slug) && $slug !== '') {
            $car = Vehicle::where('slug', $slug)->firstOrFail();
            $data = ['car' => $car, 'c' => Stats::car($car, $days), 'inner' => 'admin.stats._car'];
        } else {
            $data = ['car' => null, 's' => Stats::summary($days, $frag ? 12 : 40), 'inner' => 'admin.stats._body'];
        }
        $data += ['days' => $days, 'frag' => $frag];

        return $frag
            ? response()->view($data['inner'], $data)->header('Cache-Control', 'no-store')
            : view('admin.stats.index', $data);
    }
}
