<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Support\Img;
use App\Support\Marques;
use App\Support\Schema;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * What machines read about the site, built from the database on every request,
 * so a car added in the panel is in them the same minute.
 *
 *   /sitemap.xml   every page and every car page, with when it last changed and
 *                  its photographs (Google Images finds them here, not in the
 *                  gallery script).
 *   /llms.txt      the site in plain words for answer engines (ChatGPT, Perplexity,
 *                  Claude…): who, where, how to reach him, what is for sale now and
 *                  the home page's questions. The llmstxt.org format: a Markdown
 *                  file at the root. Only what the pages already say.
 */
class SeoController extends Controller
{
    /** The fixed pages and the view each is drawn from (its file's age is the page's lastmod). */
    private const PAGES = [
        '/'                 => 'pages.inicio',
        '/catalogo'         => 'pages.catalogo',
        '/por-que-nosotros' => 'pages.por-que',
        '/contacto'         => 'pages.contacto',
        '/vende'            => 'pages.sell-car',
        '/recomienda'       => 'pages.recomienda',
    ];

    /** A car page names at most this many photographs (Google reads up to 1,000). */
    private const PHOTOS = 60;

    public function sitemap(): Response
    {
        $cars = Vehicle::whereIn('status', ['available', 'reserved', 'sold'])
            ->orderByRaw("CASE status WHEN 'sold' THEN 1 ELSE 0 END")->orderByDesc('updated_at')
            ->get(['slug', 'status', 'updated_at', 'cover_image', 'gallery_images']);

        // the home page and the catalogue change whenever a car does
        $stock = $cars->max('updated_at');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach (self::PAGES as $path => $view) {
            $file = @filemtime(resource_path('views/' . str_replace('.', '/', $view) . '.blade.php')) ?: null;
            $at = $file ? \Carbon\Carbon::createFromTimestamp($file) : null;
            if (in_array($path, ['/', '/catalogo'], true) && $stock && (! $at || $stock->gt($at))) {
                $at = $stock;
            }
            $xml .= self::url(url($path), $at);
        }

        foreach ($cars as $car) {
            $photos = array_values(array_unique(array_filter(array_merge(
                [$car->cover_image],
                is_array($car->gallery_images) ? $car->gallery_images : []
            ))));
            // a sold car is a record: its cover is enough
            $photos = array_slice($photos, 0, $car->status === 'sold' ? 1 : self::PHOTOS);
            $images = array_values(array_filter(array_map(fn ($p) => ($u = Img::url($p, 1600)) ? url($u) : null, $photos)));
            $xml .= self::url(url('/coche/' . $car->slug), $car->updated_at, $images);
        }

        return response($xml . '</urlset>', 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function llms(): Response
    {
        $site = url('/');
        $euros = fn ($n) => number_format((int) $n, 0, ',', '.') . ' €';
        $cars = Vehicle::whereIn('status', ['available', 'reserved'])->orderByDesc('price')->get();

        $out = "# IV MOTORCLASS\n\n"
            // the site's own description (layouts/site.blade.php) and the footer's lines
            . "> Coches alemanes premium en Málaga: Volkswagen, Audi, BMW, Mercedes-Benz y Porsche. Seleccionados, revisados y con garantía. Escríbeme por WhatsApp.\n\n"
            . "Málaga, sin tienda: trabajo con cita. Quedamos donde esté el coche.\n\n"
            . "- Teléfono: +34 614 753 187\n"
            . "- WhatsApp: https://wa.me/34614753187\n"
            . '- Email: ' . Schema::EMAIL . "\n"
            . "- Horario: todos los días, a convenir. Escríbeme y quedamos a la hora que te venga bien.\n";
        foreach (HomePageController::SOCIAL as $s) {
            $out .= '- ' . $s['name'] . ': ' . $s['url'] . "\n";
        }

        $out .= "\n## Coches a la venta ahora\n\n";
        if ($cars->isEmpty()) {
            $out .= "Ahora mismo no hay coches a la venta: " . $site . "/catalogo\n";
        }
        foreach ($cars as $c) {
            $name = trim((Marques::for($c->brand)['name'] ?? Str::title((string) $c->brand)) . ' ' . $c->model . ' ' . $c->year);
            // the same facts, in the same order, as the car page's own description
            $facts = array_filter([
                $c->status === 'reserved' ? 'Reservado' : null,
                $c->price ? $euros($c->price) : null,
                $c->mileage ? number_format($c->mileage, 0, ',', '.') . ' km' : null,
                $c->fuel ?: $c->fuel_type,
                $c->transmission,
            ]);
            $out .= '- [' . $name . '](' . $site . '/coche/' . $c->slug . '): ' . implode(' · ', $facts) . "\n";
        }

        $out .= "\n## Páginas\n\n"
            . "- [Coches](" . $site . "/catalogo)\n"
            . "- [Por qué nosotros](" . $site . "/por-que-nosotros)\n"
            . "- [Contacto](" . $site . "/contacto)\n"
            . "- [Vende tu coche](" . $site . "/vende)\n"
            . "- [Recomienda a un amigo](" . $site . "/recomienda)\n";

        $out .= "\n## Preguntas frecuentes\n";
        foreach (HomePageController::FAQ as [$q, $a]) {
            $out .= "\n### " . $q . "\n\n" . $a . "\n";
        }

        return response($out, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** @param list<string> $images */
    private static function url(string $loc, $at, array $images = []): string
    {
        $x = '<url><loc>' . e($loc) . '</loc>';
        if ($at) {
            $x .= '<lastmod>' . $at->toAtomString() . '</lastmod>';
        }
        foreach ($images as $img) {
            $x .= '<image:image><image:loc>' . e($img) . '</image:loc></image:image>';
        }

        return $x . "</url>\n";
    }
}
