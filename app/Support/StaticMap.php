<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * The map on /contacto and in the footer, as our own image.
 *
 * A Google Maps embed costs the page the moment it is on screen: its own
 * scripts, fonts and tiles, and Google's cookies (which the consent notice
 * would then have to cover). So the page shows a picture of the map instead —
 * OpenStreetMap tiles fetched ONCE, stitched, and recoloured into the site's
 * palette — and the live Google map is only built when someone presses
 * "Activar el mapa". Until then there is not a single request to Google.
 *
 * Run `php artisan map:render` after changing the point or the palette. The
 * tiles are cached in storage/app/map-tiles, so a re-render fetches nothing.
 * Tile use follows the OSM tile policy: a one-off handful, a real User-Agent,
 * and "© OpenStreetMap" shown on every image (see the views).
 *
 * The point is the image's centre, so the pin is simply drawn at 50% / 50% in
 * CSS — crisp at any density, and in the right place under any object-fit
 * crop that keeps the centre.
 */
final class StaticMap
{
    /** Málaga. There is no showroom; the city is the location. */
    public const LAT = 36.7213;
    public const LON = -4.4214;

    /** name => [width, height, zoom, palette] */
    public const OUT = [
        'malaga-wide'  => [2000, 875, 13, 'light'],   // /contacto from 760px: 16:7
        'malaga-phone' => [800, 600, 12, 'light'],    // /contacto on a phone: 4:3, drawn at 2x
        'malaga-foot'  => [640, 400, 12, 'dark'],     // the footer, on navy: 16:10, 2x
    ];

    /**
     * Brightness (the tile pixel's brightest channel, 0-255) to colour.
     * The brightest channel rather than luminance, because OSM's roads are
     * orange and yellow: by luminance a main road came out DARKER than the land
     * around it, which is a map drawn inside out.
     */
    private const RAMP = [
        'light' => [[0, '#111C2E'], [60, '#475467'], [150, '#9AA5B5'], [215, '#D5DCE6'], [240, '#E9EDF3'], [255, '#FFFFFF']],
        'dark'  => [[0, '#040B1F'], [150, '#0B1A36'], [215, '#0F2345'], [240, '#112A52'], [255, '#2C4B78']],
    ];
    private const WATER = ['light' => '#CCDBF0', 'dark' => '#06132B'];

    public static function renderAll(?callable $say = null): void
    {
        foreach (self::OUT as $name => [$w, $h, $z, $pal]) {
            $path = public_path("img/map/{$name}.webp");
            self::render($w, $h, $z, $pal, $path);
            $say && $say(sprintf('%s  %dx%d z%d  %s KB', $name, $w, $h, $z, round(filesize($path) / 1024)));
        }
    }

    private static function render(int $w, int $h, int $z, string $pal, string $path): void
    {
        $n = 2 ** $z;
        $cx = (self::LON + 180) / 360 * $n * 256;
        $lat = deg2rad(self::LAT);
        $cy = (1 - log(tan($lat) + 1 / cos($lat)) / M_PI) / 2 * $n * 256;
        $x0 = $cx - $w / 2;
        $y0 = $cy - $h / 2;

        $img = imagecreatetruecolor($w, $h);
        for ($tx = (int) floor($x0 / 256); $tx <= (int) floor(($x0 + $w - 1) / 256); $tx++) {
            for ($ty = (int) floor($y0 / 256); $ty <= (int) floor(($y0 + $h - 1) / 256); $ty++) {
                $tile = imagecreatefromstring(self::tile($z, $tx, $ty));
                imagecopy($img, $tile, (int) round($tx * 256 - $x0), (int) round($ty * 256 - $y0), 0, 0, 256, 256);
                imagedestroy($tile);
            }
        }

        $lut = self::lut($pal);
        $water = self::rgb(self::WATER[$pal]);
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorat($img, $x, $y);
                $r = ($c >> 16) & 255; $g = ($c >> 8) & 255; $b = $c & 255;
                // OSM water is #AAD3DF: blue well above red. Nothing else on the
                // standard style is that far from grey in that direction.
                $isWater = $b - $r > 40 && $g - $r > 25;
                [$or, $og, $ob] = $isWater ? $water : $lut[max($r, $g, $b)];
                imagesetpixel($img, $x, $y, ($or << 16) | ($og << 8) | $ob);
            }
        }

        @mkdir(dirname($path), 0775, true);
        imagewebp($img, $path, $w > 1000 ? 60 : 72);   // the wide one is 2000px; 60 halves it with no visible loss on a pale map
        imagedestroy($img);
    }

    private static function tile(int $z, int $x, int $y): string
    {
        $file = storage_path("app/map-tiles/{$z}-{$x}-{$y}.png");
        if (is_file($file)) {
            return file_get_contents($file);
        }
        $png = Http::withHeaders(['User-Agent' => 'ivmotorclass.com static map (one-off render; jvmotorclass@gmail.com)'])
            ->timeout(20)->get("https://tile.openstreetmap.org/{$z}/{$x}/{$y}.png")->throw()->body();
        @mkdir(dirname($file), 0775, true);
        file_put_contents($file, $png);
        usleep(250000);   // be polite: one tile at a time

        return $png;
    }

    /** @return array<int, array{int,int,int}> 256 entries, brightness → rgb */
    private static function lut(string $pal): array
    {
        $stops = array_map(fn ($s) => [$s[0], self::rgb($s[1])], self::RAMP[$pal]);
        $out = [];
        for ($v = 0; $v < 256; $v++) {
            for ($i = 0; $i < count($stops) - 1 && $v > $stops[$i + 1][0]; $i++);
            [$a, $ca] = $stops[$i];
            [$b, $cb] = $stops[min($i + 1, count($stops) - 1)];
            $t = $b === $a ? 0 : ($v - $a) / ($b - $a);
            $out[$v] = array_map(fn ($p, $q) => (int) round($p + ($q - $p) * $t), $ca, $cb);
        }

        return $out;
    }

    private static function rgb(string $hex): array
    {
        return array_map('hexdec', str_split(ltrim($hex, '#'), 2));
    }
}
