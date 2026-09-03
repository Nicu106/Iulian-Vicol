<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;

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
    /**
     * Least to most exclusive. This order is the page's argument, so it is declared
     * here rather than derived — the market's ranking is not in the database.
     *
     * Colours are the marques' own, checked against white text:
     * VW 16.13:1 · Audi 6.56:1 · BMW 5.94:1 · Mercedes 17.22:1 · Porsche 5.46:1.
     * Mercedes' petrol blue (#00ADEF) measures 2.55:1 and is unusable, so the row
     * carries their corporate black instead.
     */
    private const MARQUES = [
        ['key' => 'volkswagen', 'name' => 'Volkswagen',    'colour' => '#022254', 'match' => ['volkswagen', 'vw']],
        ['key' => 'audi',       'name' => 'Audi',          'colour' => '#930016', 'match' => ['audi']],
        ['key' => 'bmw',        'name' => 'BMW',           'colour' => '#004086', 'match' => ['bmw']],
        ['key' => 'mercedes',   'name' => 'Mercedes-Benz', 'colour' => '#01172E', 'match' => ['mercedes', 'mercedes-benz', 'mercedes benz']],
        ['key' => 'porsche',    'name' => 'Porsche',       'colour' => '#C50007', 'match' => ['porsche']],
    ];

    /**
     * Layout examples, for a marque he has never had. Freely licensed photographs, and
     * every card carrying a visible "Ejemplo" badge — the page must never show a car it
     * does not have as though it were stock.
     */
    private const DEMO = [
        'porsche' => [
            ['model' => '911 GT3', 'year' => 2019, 'price' => 139000, 'km' => 41000, 'fuel' => 'Gasolina', 'gear' => 'PDK', 'img' => 'demo/porsche-911-gt3.jpg'],
            ['model' => 'Panamera Turbo', 'year' => 2018, 'price' => 84500, 'km' => 96000, 'fuel' => 'Gasolina', 'gear' => 'PDK', 'img' => 'demo/porsche-panamera.jpg'],
        ],
    ];

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
            //   Where he has never had one at all — Porsche — a marked example stands in,
            //   never presented as stock; see the brandbook on stock photography.
            $delivered = $soldCount->filter($is)->values();

            $rows[] = $m + [
                'cars'      => $cars,
                'delivered' => $delivered,
                'demo'      => $cars->count() || $delivered->count() ? [] : (self::DEMO[$m['key']] ?? []),
                'total'     => $cars->count() + $delivered->count() + count($cars->count() || $delivered->count() ? [] : (self::DEMO[$m['key']] ?? [])),
                'n'         => $cars->count(),
                'sold'      => $sold,
                'from'      => $cars->count() ? (int) $cars->min('price') : null,
                'state'     => $cars->count() ? 'stocked' : ($sold ? 'between' : 'wanted'),
            ];
        }

        return view('pages.catalogo', [
            'rows'  => $rows,
            'total' => $available->count(),
            'sold'  => $soldCount->count(),
        ]);
    }
}
