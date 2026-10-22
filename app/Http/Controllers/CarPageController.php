<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\View\View;

/**
 * One car, at /coche/{slug}.
 *
 * The photographs are the page. A used car is bought on what the buyer can see,
 * and this dealer's own photographs — 9 to 58 of them per car — are the whole
 * argument. They arrive here sorted into what the buyer is actually asking at
 * each moment: what it looks like, what it is like inside, and what is wrong
 * with it.
 */
class CarPageController extends Controller
{
    /** The order the tabs appear in, and the word for each. */
    private const GROUPS = [
        'exterior' => 'Exterior',
        'interior' => 'Interior',
        'flaw'     => 'Imperfecciones',
    ];

    public function show(string $slug): View
    {
        // Not `pending`: a submission from /vende is a Vehicle row until he approves
        // it, and this page must not show a stranger's unreviewed car at a guessable
        // URL. Anything he has not approved does not exist here.
        $car = Vehicle::where('slug', $slug)->whereIn('status', ['available', 'reserved', 'sold'])->firstOrFail();   // not pending, rejected or draft

        // array_unique: when the cover is one of the gallery photos — which is what
        // the form does when he does not upload a separate one — it was listed twice
        // and "Todas" counted one photo more than there is.
        $all = array_values(array_unique(array_filter(array_merge(
            [$car->cover_image],
            is_array($car->gallery_images) ? $car->gallery_images : []
        ))));

        $tags = is_array($car->image_tags) ? $car->image_tags : [];

        // Where each photograph sits in the order he set in the panel (drag and
        // drop, 2026-10-17). "Todas" opens on the cover — the photograph the buyer
        // tapped in the catalogue — and then follows that order; a group's tab
        // follows it exactly, the cover included wherever he put it. A cover that
        // is a separate file outside the gallery has no place in it: first.
        $at = array_flip(array_values(is_array($car->gallery_images) ? $car->gallery_images : []));

        // Every photograph carries its own group, so the markup can be filtered
        // without asking the server again. Untagged ones are not invented into a
        // group — they simply appear under "Todas", which is what makes partial
        // tagging safe.
        $photos = [];
        foreach ($all as $i => $path) {
            $photos[] = [
                'path'  => $path,
                'group' => $tags[$path] ?? null,
                'n'     => $i + 1,
                'o'     => $at[$path] ?? -1,
            ];
        }

        $counts = ['all' => count($photos)];
        foreach (array_keys(self::GROUPS) as $g) {
            $counts[$g] = count(array_filter($photos, fn ($p) => $p['group'] === $g));
        }

        return view('pages.coche', [
            'car'    => $car,
            // The marque's colour for the wall at the top; null for a brand he does
            // not specialise in, which gets the site's own ink instead.
            'marque' => \App\Support\Marques::for($car->brand),
            // the marque's own spelling ("BMW", "Mercedes-Benz"), whatever the data says ("Bmw")
            'brand'  => \App\Support\Marques::for($car->brand)['name'] ?? \Illuminate\Support\Str::title((string) $car->brand),
            'photos' => $photos,
            // The video, if the link is one YouTube video: id, start, and the still
            // that exists for it (App\Support\YouTube). Nothing from YouTube is
            // loaded until it is pressed.
            'video'  => ($v = \App\Support\YouTube::parse($car->video_url))
                            ? $v + ['still' => \App\Support\YouTube::still($v['id'])] : null,
            'groups' => self::GROUPS,
            'counts' => $counts,
            // The short list, beside the price: what decides a viewing.
            'specs'  => array_filter([
                'Año'          => $car->year,
                'Kilómetros'   => $car->mileage ? number_format($car->mileage, 0, ',', '.') . ' km' : null,
                'Combustible'  => $car->fuel ?: $car->fuel_type,
                'Cambio'       => $car->transmission,
                'Potencia'     => $car->power ? $car->power . ' CV' : null,
                // a DGT label typed into the old "Etiqueta" field landed here; it is not a state
                'Estado'       => in_array(strtoupper((string) $car->condition), ['0', 'ECO', 'C', 'B'], true) ? null : $car->condition,
            ]),

            // The long list, further down: everything else that is known.
            'tech'   => array_filter([
                'Marca'                  => \App\Support\Marques::for($car->brand)['name'] ?? $car->brand,
                'Modelo'                 => $car->model,
                'Motor'                  => $car->engine ?: $car->engine_capacity,
                'Carrocería'             => $car->body_type,
                'Tracción'               => $car->drivetrain,
                'Color'                  => $car->color,
                'Etiqueta medioambiental'=> $car->vin ?: ($car->sheet['label'] ?? null),   // the DGT label lives in this column
                // from the coches.net sheet (Admin\SheetController), when there is one
                'Puertas'                => $car->sheet['doors'] ?? null,
                'Plazas'                 => $car->sheet['seats'] ?? null,
                'Maletero'               => !empty($car->sheet['trunk']) ? number_format($car->sheet['trunk'], 0, ',', '.') . ' litros' : null,
                'Emisiones de CO2'       => !empty($car->sheet['co2']) ? $car->sheet['co2'] . ' g/km' : null,
            ]),

            // Its past, from the coches.net sheet: what it cost new in Spain with the
            // extras it carries, against today's price, and what the dealer's own
            // description says (registration, ITV, keys, accidents). Null without one.
            'past'   => self::past($car),

            // the warranty included with THIS car (the sheet says 1 or 2 years)
            'warranty' => max(12, (int) ($car->sheet['warranty_months'] ?? 12)),

            // Price. Only what is true: what it costs, and what it cost before if that
            // is recorded. The live site puts a "market average" beside this, generated
            // with random_int(1200,1800) — a number invented on every page load. It is
            // not carried over; see the brandbook's sign-off table.
            'price'  => [
                'now'    => (int) $car->price,
                'before' => $car->original_price && $car->original_price > $car->price
                                ? (int) $car->original_price : null,
                'off'    => $car->original_price && $car->original_price > $car->price
                                ? (int) $car->original_price - (int) $car->price : null,
            ],

            'tags'   => [],   // SEO keywords, not for buyers (they read as raw text on the page)

            // The end of the page is not a dead end: what else is here now. Same
            // marque first, then the nearest in price. Only cars for sale.
            'others' => Vehicle::where('status', 'available')->where('id', '!=', $car->id)->get()
                ->sortBy(fn ($v) => [
                    ((\App\Support\Marques::for($v->brand)['key'] ?? $v->brand) === (\App\Support\Marques::for($car->brand)['key'] ?? $car->brand)) ? 0 : 1,
                    abs((int) $v->price - (int) $car->price),
                ])->take(6)->values(),
            'chips'  => BrandCatalogController::chips(),
            'sub'    => BrandCatalogController::sub(),
            'km'     => fn ($n) => number_format((int) $n, 0, ',', '.') . ' km',
            'euros'  => fn ($n) => number_format((int) $n, 0, ',', '.') . ' €',
        ]);
    }

    /** the "Su historia" block: then against now, the factory extras, the facts */
    public static function past(Vehicle $car): ?array
    {
        $s = $car->sheet;
        if (!is_array($s)) return null;
        $total = (float) ($s['new_total'] ?? 0);
        $now = (int) $car->price;
        $extras = collect($s['extras'] ?? [])->filter(fn ($x) => ($x['price'] ?? 0) > 0)->sortByDesc('price')
            ->map(fn ($x) => ['name' => $x['name'], 'price' => (int) round($x['price'])])->values()->all();
        $facts = \App\Support\CochesNetSheet::factLines($s['facts'] ?? []);
        if (($total <= $now || $now <= 0) && !$extras && !$facts) return null;
        return [
            'year'   => $s['new_year'] ?? null,
            // rounded, not cut: 49.990,6 € is 49.991 € here, on the placard and in the form
            'total'  => $total > $now && $now > 0 ? (int) round($total) : null,
            'base'   => isset($s['new_price']) ? (int) round($s['new_price']) : null,
            'xtotal' => isset($s['extras_total']) ? (int) round($s['extras_total']) : null,
            'now'    => $now,
            'save'   => $total > $now ? (int) round($total - $now) : null,
            'pct'    => $total > $now && $total > 0 ? (int) floor(($total - $now) / $total * 100) : null,
            'extras' => $extras,
            'facts'  => $facts,
        ];
    }
}
