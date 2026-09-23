<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * The map on /contacto and in the footer, drawn by us.
 *
 * A Google Maps embed costs the page the moment it is on screen: its scripts,
 * fonts and tiles, and Google's cookies. So the page shows our own drawing of
 * the place, and the live Google map is only built when "Activar el mapa" is
 * pressed.
 *
 * The drawing is VECTOR, from OpenStreetMap data: the coastline, the sea, the
 * rivers and the main roads — nothing else. The first version recoloured OSM's
 * raster tiles, and every label, contour hatch and land-use texture came along
 * as grey dust; in the footer, at 320px, it read as a dirty photograph of a
 * map (the client: "arată foarte foarte rău"). Lines only: sharp at any size,
 * a few KB, in the site's own colours. Place names are ours, in HTML over the
 * image (see pos()), so they are set in the site's typeface.
 *
 * `php artisan map:render` draws the files. The OSM extract is fetched once
 * from Overpass and cached in storage/app/map-osm.json; delete it to refetch.
 * Data © OpenStreetMap contributors (ODbL) — credited on every map.
 */
final class StaticMap
{
    /** Málaga. There is no showroom; the city is the location. */
    public const LAT = 36.7213;
    public const LON = -4.4214;

    /** Málaga airport: people fly in to see a car, so it is marked on /contacto. */
    public const AIRPORT = [36.6749, -4.4991];

    /**
     * name => [width, height, zoom, palette]. The image IS the box's ratio
     * (16:7, 4:3, 16:10), so nothing is cropped and a percentage from pos()
     * lands on the right spot.
     */
    public const OUT = [
        'malaga-wide'  => [2000, 875, 13, 'light'],
        'malaga-phone' => [800, 600, 12, 'light'],
        'malaga-foot'  => [640, 400, 12, 'dark'],
    ];

    /**
     * Land, sea, coast line, rivers, and the three road classes with their widths.
     * Dark: the footer's own navy, land a step above --mc-deep and the sea a step
     * below it, so the coast is drawn by the fields and the line only sharpens it.
     */
    private const STYLE = [
        'light' => ['land' => '#EEF1F6', 'sea' => '#D5E0F1', 'coast' => '#9FB3D4', 'river' => '#C3D2EA',
                    'roads' => [['#A6B1C2', 2.2], ['#BFC8D5', 1.4], ['#D3DAE4', 1.0]]],
        'dark'  => ['land' => '#0A1731', 'sea' => '#040B1F', 'coast' => '#4A6CA3', 'river' => '#15305A',
                    'roads' => [['#5A78AA', 1.3], ['#34507D', 0.9], ['#22395F', 0.7]]],
    ];

    private const ROAD_CLASS = ['motorway' => 0, 'trunk' => 0, 'primary' => 1, 'secondary' => 2];

    public static function renderAll(?callable $say = null): void
    {
        $osm = self::osm();
        foreach (self::OUT as $name => [$w, $h, $z, $pal]) {
            $path = public_path("img/map/{$name}.svg");
            @mkdir(dirname($path), 0775, true);
            file_put_contents($path, self::svg($osm, $w, $h, $z, self::STYLE[$pal]));
            $say && $say(sprintf('%s  %dx%d z%d  %s KB', $name, $w, $h, $z, round(filesize($path) / 1024, 1)));
        }
    }

    /** Where a point falls on one of the maps, in percent of its box. */
    public static function pos(string $name, float $lat, float $lon): array
    {
        [$w, $h, $z] = self::OUT[$name];
        [$x, $y] = self::project($lat, $lon, $w, $h, $z);

        return [round($x / $w * 100, 2), round($y / $h * 100, 2)];
    }

    private static function svg(array $osm, int $w, int $h, int $z, array $st): string
    {
        $p = fn ($g) => self::project($g['lat'], $g['lon'], $w, $h, $z);
        $inView = function (array $pts) use ($w, $h) {
            foreach ($pts as [$x, $y]) {
                if ($x > -50 && $x < $w + 50 && $y > -50 && $y < $h + 50) return true;
            }
            return false;
        };

        // The coast: OSM draws coastline ways with the land on the left, so they
        // chain end to start into lines that have the sea on their right. Closed
        // rings are islands and breakwaters. The longest open chain is the shore;
        // the sea is that chain closed round the bottom of the frame.
        $open = $rings = [];
        foreach ($osm as $e) {
            if (($e['tags']['natural'] ?? null) !== 'coastline') continue;
            $pts = array_map($p, $e['geometry']);
            $pts[0] == end($pts) ? $rings[] = $pts : $open[] = $pts;
        }
        $shore = self::chain($open);
        usort($shore, fn ($a, $b) => count($b) <=> count($a));
        $coast = $shore[0] ?? [];
        $pad = 400;
        $sea = $coast ? array_merge($coast, [[end($coast)[0], $h + $pad], [$coast[0][0], $h + $pad]]) : [];

        $out = [];
        $out[] = sprintf('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" preserveAspectRatio="xMidYMid slice">', $w, $h);
        $out[] = sprintf('<rect width="%d" height="%d" fill="%s"/>', $w, $h, $st['land']);
        if ($sea) $out[] = sprintf('<path d="%s Z" fill="%s"/>', self::d(self::simplify($sea, 0.5)), $st['sea']);
        foreach ($rings as $r) {
            if ($inView($r)) $out[] = sprintf('<path d="%s Z" fill="%s"/>', self::d(self::simplify($r, 0.5)), $st['land']);
        }

        $lines = function (array $ways, string $colour, float $width) use ($inView) {
            $d = [];
            foreach ($ways as $pts) {
                if ($inView($pts)) $d[] = self::d(self::simplify($pts, 0.9));
            }
            return $d ? sprintf('<path d="%s" fill="none" stroke="%s" stroke-width="%s" stroke-linecap="round" stroke-linejoin="round"/>',
                implode(' ', $d), $colour, $width) : '';
        };

        $rivers = [];
        $roads = [[], [], []];
        foreach ($osm as $e) {
            $t = $e['tags'];
            if (($t['waterway'] ?? null) === 'river') $rivers[] = array_map($p, $e['geometry']);
            if (isset($t['highway'], self::ROAD_CLASS[$t['highway']])) $roads[self::ROAD_CLASS[$t['highway']]][] = array_map($p, $e['geometry']);
        }
        $out[] = $lines($rivers, $st['river'], 1.6);
        // smallest first, so the motorways are drawn over what they cross
        for ($i = 2; $i >= 0; $i--) $out[] = $lines($roads[$i], $st['roads'][$i][0], $st['roads'][$i][1]);
        if ($coast) $out[] = $lines([$coast, ...$rings], $st['coast'], 1.2);
        $out[] = '</svg>';

        return implode("\n", array_filter($out));
    }

    /** Web Mercator, in the frame's own pixels, centred on the point. */
    private static function project(float $lat, float $lon, int $w, int $h, int $z): array
    {
        $px = fn ($la, $lo) => [
            ($lo + 180) / 360 * (2 ** $z) * 256,
            (1 - log(tan(deg2rad($la)) + 1 / cos(deg2rad($la))) / M_PI) / 2 * (2 ** $z) * 256,
        ];
        [$cx, $cy] = $px(self::LAT, self::LON);
        [$x, $y] = $px($lat, $lon);

        return [$x - $cx + $w / 2, $y - $cy + $h / 2];
    }

    /** Join lines whose end is another's start. */
    private static function chain(array $parts): array
    {
        $key = fn ($pt) => round($pt[0], 3) . ',' . round($pt[1], 3);
        $done = [];
        while ($parts) {
            $line = array_shift($parts);
            $grew = true;
            while ($grew) {
                $grew = false;
                foreach ($parts as $i => $q) {
                    if ($key(end($line)) === $key($q[0])) { $line = array_merge($line, array_slice($q, 1)); unset($parts[$i]); $grew = true; }
                    elseif ($key(end($q)) === $key($line[0])) { $line = array_merge($q, array_slice($line, 1)); unset($parts[$i]); $grew = true; }
                }
            }
            $done[] = $line;
        }

        return $done;
    }

    /** Ramer–Douglas–Peucker: drop the points no one could see. */
    private static function simplify(array $pts, float $eps): array
    {
        if (count($pts) < 3) return $pts;
        [$ax, $ay] = $pts[0];
        [$bx, $by] = end($pts);
        $dx = $bx - $ax; $dy = $by - $ay; $len = hypot($dx, $dy) ?: 1e-9;
        $max = 0; $at = 0;
        for ($i = 1, $n = count($pts) - 1; $i < $n; $i++) {
            $d = abs($dy * $pts[$i][0] - $dx * $pts[$i][1] + $bx * $ay - $by * $ax) / $len;
            if ($d > $max) { $max = $d; $at = $i; }
        }
        if ($max <= $eps) return [$pts[0], end($pts)];

        return array_merge(
            array_slice(self::simplify(array_slice($pts, 0, $at + 1), $eps), 0, -1),
            self::simplify(array_slice($pts, $at), $eps)
        );
    }

    private static function d(array $pts): string
    {
        $s = '';
        foreach ($pts as $i => [$x, $y]) $s .= ($i ? 'L' : 'M') . (int) round($x) . ' ' . (int) round($y);   // whole units: the small maps are drawn at 2x, so a unit is half a CSS pixel

        return $s;
    }

    private static function osm(): array
    {
        $file = storage_path('app/map-osm.json');
        if (! is_file($file)) {
            $q = '[out:json][timeout:60];(way["natural"="coastline"](36.55,-4.75,36.90,-4.10);'
               . 'way["highway"~"^(motorway|trunk|primary|secondary)$"](36.55,-4.75,36.90,-4.10);'
               . 'way["waterway"="river"](36.55,-4.75,36.90,-4.10););out geom;';
            $body = Http::asForm()->withHeaders(['User-Agent' => 'ivmotorclass.com map render (jvmotorclass@gmail.com)'])
                ->timeout(120)->post('https://overpass-api.de/api/interpreter', ['data' => $q])->throw()->body();
            file_put_contents($file, $body);
        }

        return json_decode(file_get_contents($file), true)['elements'];
    }
}
