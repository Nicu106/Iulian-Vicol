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
        $car = Vehicle::where('slug', $slug)->firstOrFail();

        $all = array_values(array_filter(array_merge(
            [$car->cover_image],
            is_array($car->gallery_images) ? $car->gallery_images : []
        )));

        $tags = is_array($car->image_tags) ? $car->image_tags : [];

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
            ];
        }

        $counts = ['all' => count($photos)];
        foreach (array_keys(self::GROUPS) as $g) {
            $counts[$g] = count(array_filter($photos, fn ($p) => $p['group'] === $g));
        }

        return view('pages.coche', [
            'car'    => $car,
            'photos' => $photos,
            'groups' => self::GROUPS,
            'counts' => $counts,
            'specs'  => array_filter([
                'Año'          => $car->year,
                'Kilómetros'   => $car->mileage ? number_format($car->mileage, 0, ',', '.') . ' km' : null,
                'Combustible'  => $car->fuel ?: $car->fuel_type,
                'Cambio'       => $car->transmission,
                'Potencia'     => $car->power,
                'Motor'        => $car->engine ?: $car->engine_capacity,
                'Color'        => $car->color,
                'Estado'       => $car->condition,
            ]),
            'euros'  => fn ($n) => number_format((int) $n, 0, ',', '.') . ' €',
        ]);
    }
}
