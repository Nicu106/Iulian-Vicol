<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Support\Marques;

/**
 * The catalogue, organised by marque rather than by filter.
 *
 * The dealer works in German premium marques only, so the page IS that statement:
 * one row per marque, ordered by how the market ranks them, each row sweeping its own
 * brand colour before its cars arrive. There is no filter bar — with nine cars a filter
 * UI advertises an inventory that does not exist (see the brandbook, section 2).
 */
class BrandCatalogController extends Controller
{
    /** Least to most exclusive; the list and its colours live in App\Support\Marques. */
    private const MARQUES = Marques::ALL;

    /**
     * A marque with no car at all — never had one, nothing in stock — shows this
     * many "Próximamente" cards: a full band at the widest ladder step, trimmed in
     * CSS to what each width shows. There used to be two invented Porsches here
     * (Unsplash photographs, marked "Ejemplo"); removed on 2026-09-23 because the
     * page must not show cars that do not exist, marked or not.
     */
    private const SOON = 4;

    public function index()
    {
        $available = Vehicle::where('status', 'available')->orderByDesc('price')->get();
        $soldCount = Vehicle::where('status', 'sold')->get();

        $rows = [];
        foreach (self::MARQUES as $m) {
            // Brand names drift in the data — "Mercedes", "Mercedes-benz", "Bmw" are all
            // in there. Match on a normalised needle rather than on the stored string.
            $is = fn ($v) => in_array(mb_strtolower(trim((string) $v->brand)), $m['match'], true);

            $cars = $available->filter($is)->values();
            $sold = $soldCount->filter($is)->count();

            // Every car of the marque he has ever had, not only what is in stock today.
            // Nine available cars across five marques left rows of two, which made the
            // page look emptier than the business is: he has had 41. The delivered ones
            // are his own photographs and carry a visible "Entregado" badge, so the row
            // is a record of the marque rather than a shelf with two things on it.
            //   Where he has never had one at all, the row is "Próximamente" cards:
            //   empty places, not invented cars.
            $delivered = $soldCount->filter($is)->values();

            $rows[] = $m + [
                'cars'      => $cars,
                'delivered' => $delivered,
                'soon'      => $cars->count() || $delivered->count() ? 0 : self::SOON,
                'total'     => $cars->count() + $delivered->count(),
                'n'         => $cars->count(),
                'sold'      => $sold,
                'from'      => $cars->count() ? (int) $cars->min('price') : null,
                'state'     => $cars->count() ? 'stocked' : ($sold ? 'between' : 'wanted'),
            ];
        }

        $chips = self::chips();
        $sub   = self::sub();

        return view('pages.catalogo', [
            'chips' => $chips,
            'sub'   => $sub,
            'rows'  => $rows,
            'total' => $available->count(),
            'sold'  => $soldCount->count(),
        ]);
    }

    /** What a chip says — one rule for every card on the site. */
    public static function chips(): \Closure
    {
        $get = fn ($c, $k) => is_array($c) ? ($c[$k] ?? null) : ($c->$k ?? null);
        $tidy = function (?string $v): ?string {
            $v = trim((string) $v);
            if ($v === '') return null;
            $map = ['diesel' => 'Diésel', 'diésel' => 'Diésel', 'gasolina' => 'Gasolina',
                    'automatica' => 'Automático', 'automática' => 'Automático', 'automático' => 'Automático',
                    'manual' => 'Manual', 'pdk' => 'PDK', 'dsg' => 'DSG'];
            return $map[mb_strtolower($v)] ?? $v;
        };
        return function ($c) use ($get, $tidy): array {
            // power is stored as "258", "150cv", "170 CV" — the number is the fact,
            // the unit is ours to add once. Without this it read "150cv CV".
            $power = preg_replace('/\D+/', '', (string) $get($c, 'power'));
            return array_values(array_filter([
                $get($c, 'year'),
                $tidy($get($c, 'fuel')),
                $tidy($get($c, 'gear') ?? $get($c, 'transmission')),
                $power !== '' ? $power . ' CV' : null,
            ]));
        };
    }

    /** The version line under the name. */
    public static function sub(): \Closure
    {
        $get = fn ($c, $k) => is_array($c) ? ($c[$k] ?? null) : ($c->$k ?? null);
        return function ($c) use ($get): ?string {
            $parts = array_filter([
                $get($c, 'engine') ?: $get($c, 'engine_capacity'),
                $get($c, 'body_type'),
                $get($c, 'drivetrain'),
            ]);
            return $parts ? implode(' · ', $parts) : null;
        };
    }
}
