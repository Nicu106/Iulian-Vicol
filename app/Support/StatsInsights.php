<?php

namespace App\Support;

use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * What the counters say about buying and photographing, for Estadísticas
 * (App\Support\Stats keeps the counters; this reads them).
 *
 *   brands()   per marque: cars for sale, the people who looked at them, per car,
 *              WhatsApp, and what was asked for without being for sale (searches,
 *              rows opened, "Avísame cuando entre uno").
 *   prices()   the same by price band.
 *   places()   where the visitors are (GeoIP town, Stats::view), for the map.
 *   photos()   how far into a car's photographs people get, and which kind holds them.
 *   carPhotos() one car's photographs, each with how many saw it and for how long.
 *   history()  the fortnight before the counting moved to the browser, estimated
 *              from the server's log (stats:history), and the cars sold, by marque.
 *
 * Every sentence it writes has a floor: under it, the page says there is not
 * enough yet rather than draw a conclusion from three people.
 */
final class StatsInsights
{
    /** People who looked at cars, under which no marque or price is called a winner. */
    public const ENOUGH = 30;

    public const BANDS = [
        [0, 10000, 'Hasta 10.000 €'],
        [10000, 15000, '10.000–15.000 €'],
        [15000, 20000, '15.000–20.000 €'],
        [20000, 25000, '20.000–25.000 €'],
        [25000, 35000, '25.000–35.000 €'],
        [35000, PHP_INT_MAX, 'Más de 35.000 €'],
    ];

    private const MALAGA = [36.7213, -4.4214];

    /** Mainland Spain, south-west and north-east corners (lat, lon). */
    private const SPAIN = [35.9, -9.4, 43.8, 3.4];

    /* ---- marques -------------------------------------------------------- */

    /** @return array{rows:list<array>,say:list<string>,people:int,enough:bool} */
    public static function brands(array $by): array
    {
        $cars = Vehicle::query()->whereIn('status', ['available', 'reserved', 'sold'])
            ->get(['slug', 'brand', 'price', 'status']);

        $rows = [];
        foreach (Marques::ALL as $m) {
            $rows[$m['key']] = self::brandRow($m['key'], $m['name']);
        }
        $total = $totalStock = $peopleStock = 0;
        foreach ($cars as $c) {
            [$k, $name] = self::marque($c->brand);
            $rows[$k] ??= self::brandRow($k, $name);
            $r = &$rows[$k];
            $path = '/coche/' . $c->slug;
            $people = (int) ($by['carp'][$c->slug] ?? 0);
            $forSale = $c->status !== 'sold';
            $r['people'] += $people;
            $r['views'] += (int) ($by['car'][$c->slug] ?? 0);
            $r['wa'] += (int) ($by['wa'][$path] ?? 0) + (int) ($by['tel'][$path] ?? 0);
            $r['timen'] += (int) ($by['timen'][$path] ?? 0);
            $r['time'] += (int) ($by['time'][$path] ?? 0);
            if ($forSale) {
                $r['stock']++;
                $r['peopleStock'] += $people;
                $r['from'] = $r['from'] === null ? (int) $c->price : min($r['from'], (int) $c->price);
                $totalStock++;
                $peopleStock += $people;
            } else {
                $r['sold']++;
            }
            $total += $people;
            unset($r);
        }

        foreach ($rows as $k => &$r) {
            $r['searches'] = (int) ($by['search']['marca:' . $k] ?? 0);
            $r['opened'] = (int) ($by['evk']['marca-abrir|' . $k] ?? 0);
            $r['slid'] = (int) ($by['evk']['marca-desliza|' . $k] ?? 0);
            $r['ask'] = (int) ($by['evk']['avisame|' . $k] ?? 0);
            $r['seen'] = (int) ($by['evk']['marca-vista|' . $k] ?? 0);
            $r['asked'] = $r['searches'] + $r['opened'] + $r['ask'];
            $r['perCar'] = $r['stock'] ? round($r['peopleStock'] / $r['stock'], 1) : null;
            $r['share'] = $total ? (int) round(100 * $r['people'] / $total) : 0;
            $r['stockShare'] = $totalStock ? (int) round(100 * $r['stock'] / $totalStock) : 0;
            $r['time'] = $r['timen'] ? (int) round($r['time'] / $r['timen']) : null;
        }
        unset($r);

        // a marque sold long ago that nobody looks at or asks for is history, not a decision (it is listed under "Lo que has vendido")
        $rows = array_values(array_filter($rows, fn ($r) => $r['stock'] || $r['people'] || $r['asked']));
        usort($rows, fn ($a, $b) => [$b['people'], $b['asked'], $b['stock']] <=> [$a['people'], $a['asked'], $a['stock']]);

        return ['rows' => $rows, 'say' => self::brandSay($rows, $total, $totalStock), 'people' => $total, 'enough' => $total >= self::ENOUGH];
    }

    /** @return list<string> */
    private static function brandSay(array $rows, int $total, int $stock): array
    {
        $say = [];
        if ($total < self::ENOUGH) {
            $say[] = $total
                ? 'Todavía son pocos datos: ' . $total . ' ' . ($total === 1 ? 'persona ha' : 'personas han') . ' mirado coches. A partir de ' . self::ENOUGH . ' esto empieza a decir algo; con 100, mucho.'
                : 'Todavía nadie ha mirado un coche en estos días.';
        } else {
            $withStock = array_filter($rows, fn ($r) => $r['stock'] > 0 && $r['peopleStock'] > 0);
            if (count($withStock) >= 2) {
                usort($withStock, fn ($a, $b) => $b['perCar'] <=> $a['perCar']);
                $top = $withStock[0];
                $say[] = 'Por coche a la venta, ' . $top['name'] . ' es la que más atrae: ' . Stats::num($top['perCar'])
                    . ' personas por coche' . (isset($withStock[1]) ? ', frente a ' . Stats::num($withStock[1]['perCar']) . ' de ' . $withStock[1]['name'] : '') . '.';
            }
            foreach ($rows as $r) {
                if ($r['stock'] && $r['stockShare'] >= 10 && $r['share'] >= $r['stockShare'] + 15) {
                    $say[] = $r['name'] . ' es el ' . $r['stockShare'] . ' % de tu stock y se lleva el ' . $r['share'] . ' % de las personas: hay más interés que coches.';
                } elseif ($r['stock'] >= 2 && $r['stockShare'] >= 20 && $r['share'] <= $r['stockShare'] / 2) {
                    $say[] = $r['name'] . ' es el ' . $r['stockShare'] . ' % de tu stock pero solo el ' . $r['share'] . ' % de las personas lo mira.';
                }
            }
        }
        foreach ($rows as $r) {
            if (! $r['stock'] && $r['asked'] >= 3) {
                $say[] = 'Preguntan por ' . $r['name'] . ' (' . $r['asked'] . ' veces: búsquedas, la fila abierta, «Avísame») y no tienes ninguno a la venta.';
            }
        }

        return $say;
    }

    private static function brandRow(string $key, string $name): array
    {
        return ['key' => $key, 'name' => $name, 'stock' => 0, 'sold' => 0, 'from' => null, 'people' => 0, 'peopleStock' => 0,
            'views' => 0, 'wa' => 0, 'timen' => 0, 'time' => 0];
    }

    /** @return array{0:string,1:string} the key and the name a stored brand is grouped under */
    public static function marque(?string $brand): array
    {
        $m = Marques::for($brand);

        return $m ? [$m['key'], $m['name']] : [Str::slug((string) $brand) ?: 'otra', Str::title(trim((string) $brand)) ?: 'Otra'];
    }

    /* ---- price ---------------------------------------------------------- */

    /** @return array{rows:list<array>,say:list<string>,asked:list<array>} */
    public static function prices(array $by): array
    {
        $cars = Vehicle::query()->whereIn('status', ['available', 'reserved', 'sold'])->get(['slug', 'price', 'status']);
        $rows = array_map(fn ($b) => ['label' => $b[2], 'stock' => 0, 'people' => 0, 'wa' => 0], self::BANDS);
        $total = 0;
        foreach ($cars as $c) {
            $i = self::band((int) $c->price);
            $people = (int) ($by['carp'][$c->slug] ?? 0);
            $rows[$i]['people'] += $people;
            $rows[$i]['wa'] += (int) ($by['wa']['/coche/' . $c->slug] ?? 0);
            if ($c->status !== 'sold') {
                $rows[$i]['stock']++;
            }
            $total += $people;
        }
        foreach ($rows as &$r) {
            $r['share'] = $total ? (int) round(100 * $r['people'] / $total) : 0;
            $r['perCar'] = $r['stock'] ? round($r['people'] / $r['stock'], 1) : null;
        }
        unset($r);

        // what the home search asked for as a top price
        $asked = [];
        foreach ($by['search'] ?? [] as $k => $n) {
            if (preg_match('~^max:(\d+)$~', $k, $m)) {
                $i = self::band((int) $m[1] - 1);
                $asked[$i] = ($asked[$i] ?? 0) + $n;
            }
        }

        $say = [];
        if ($total >= self::ENOUGH) {
            $top = collect($rows)->sortByDesc('people')->first();
            $say[] = 'El ' . $top['share'] . ' % de las personas que miran coches mira los de ' . mb_strtolower($top['label']) . '.';
        }

        return ['rows' => array_values(array_filter($rows, fn ($r) => $r['stock'] || $r['people'])), 'say' => $say,
            'asked' => array_map(fn ($i, $n) => ['label' => self::BANDS[$i][2], 'n' => $n], array_keys($asked), $asked)];
    }

    private static function band(int $price): int
    {
        foreach (self::BANDS as $i => $b) {
            if ($price >= $b[0] && $price < $b[1]) {
                return $i;
            }
        }

        return 0;
    }

    /* ---- where from ------------------------------------------------------ */

    /**
     * @return array{total:int,near:int,andalucia:int,spain:int,abroad:int,cities:list<array>,
     *               regions:array<string,int>,countries:list<array>,es:?array,world:?array}
     */
    public static function places(array $geo): array
    {
        $meta = self::map('es');
        $alias = $meta['aliases'] ?? [];
        $names = [];
        foreach ($meta['regions'] ?? [] as $r) {
            $names[$r['id']] = $r['name'];
        }

        $cities = $regions = $countries = [];
        $total = $near = $andalucia = $spain = 0;
        foreach ($geo as $key => $n) {
            $g = Geo::parse((string) $key);
            if (! $g) {
                continue;
            }
            $total += $n;
            $countries[$g['cc']] = ($countries[$g['cc']] ?? 0) + $n;
            $id = $g['cc'] . '|' . $g['city'];
            $cities[$id] ??= ['city' => $g['city'] ?: ($g['region'] ?: Geo::country($g['cc'])), 'cc' => $g['cc'],
                'country' => Geo::country($g['cc']), 'lat' => $g['lat'], 'lon' => $g['lon'], 'n' => 0];
            $cities[$id]['n'] += $n;
            if ($g['cc'] === 'ES') {
                $spain += $n;
                // the name DB-IP gives, else the region the point falls in on the map itself
                $rid = $alias[$g['region']] ?? ($meta && ($g['lat'] || $g['lon']) ? self::esRegionAt($g['lat'], $g['lon'], $meta) : null);
                if ($rid !== null) {
                    $regions[$rid] = ($regions[$rid] ?? 0) + $n;
                }
                if (self::km($g['lat'], $g['lon'], ...self::MALAGA) <= 60) {
                    $near += $n;
                } elseif ($rid !== null && ($names[$rid] ?? '') === 'Andalucía') {
                    $andalucia += $n;
                }
            }
        }
        $cities = array_values($cities);
        usort($cities, fn ($a, $b) => $b['n'] <=> $a['n']);
        arsort($countries);
        $countries = array_map(fn ($cc, $n) => ['cc' => $cc, 'name' => Geo::country($cc), 'n' => $n], array_keys($countries), $countries);
        $world = self::map('world');

        return [
            'total'     => $total,
            'near'      => $near,
            'andalucia' => $andalucia,
            'spain'     => $spain,
            'abroad'    => $total - $spain,
            'cities'    => $cities,
            'regions'   => $regions,
            'names'     => $names,
            'countries' => $countries,
            'es'        => $meta ?: null,
            'spainMap'  => $meta && $spain ? self::spainMap($cities, $meta) : null,
            'wide'      => $world ? self::wide($cities, $countries, $world) : null,
        ];
    }

    /** Both maps are drawn 600 wide, so a dot and a name come out the same size on either. */
    private const MAP_W = 600;

    /**
     * The Spain map's towns: a dot each, sized by people (the biggest drawn first,
     * so a small town next to it stays on top and visible), and a name on the
     * three biggest that have room.
     *
     * @return array{dots:list<array>,names:list<array>}
     */
    private static function spainMap(array $cities, array $m): array
    {
        $towns = array_values(array_filter($cities, fn ($c) => $c['cc'] === 'ES' && ($c['lat'] || $c['lon'])));
        $max = max(1, $towns[0]['n'] ?? 1);
        $dots = [];
        foreach ($towns as $c) {
            [$x, $y] = self::esXy($c['lat'], $c['lon'], $m);
            $dots[] = ['x' => $x, 'y' => $y, 'r' => round(4 + 11 * sqrt($c['n'] / $max), 1), 'n' => $c['n'],
                'name' => $c['city'], 'title' => $c['city'] . ': ' . $c['n']];
        }

        return ['dots' => $dots, 'names' => self::names(array_slice($dots, 0, 6), self::MAP_W, $m['h'], 3)];
    }

    /**
     * The map beyond Spain, drawn as soon as someone from another country is in
     * the period: framed on the places there are, and on Málaga, so it always
     * reads from here. As tight as the data allows: Portugal or Gibraltar show
     * the Peninsula, a German shows Europe, an American the Atlantic. Every town
     * gets a dot, a country too small to draw (Gibraltar, Malta, Andorra) too;
     * towns that would sit on each other at that scale become one dot, named for
     * the biggest. Longitude is narrowed by the cosine of the middle latitude, so
     * Europe is not stretched sideways. A country known without a town is framed
     * by its main body (France, not French Guiana).
     *
     * @return array{w:int,h:int,m:string,lands:list<array>,dots:list<array>,names:list<array>}|null
     */
    public static function wide(array $cities, array $countries, array $m): ?array
    {
        $shapes = self::shapes($m);
        $k = $m['k'];
        $top = $m['latTop'];
        $bottom = $top - $m['h'] / $k;
        $pts = array_values(array_filter($cities, fn ($c) => $c['cc'] !== 'ZZ' && ($c['lat'] || $c['lon'])));
        $located = array_flip(array_column($pts, 'cc'));

        // mainland Spain is always in the frame, whole, so the map reads from here;
        // beyond it only the places abroad move the frame (a town in the Canaries
        // or Mallorca is on the Spain map already)
        $lats = [self::SPAIN[0], self::SPAIN[2]];
        $lons = [self::SPAIN[1], self::SPAIN[3]];
        $foreign = false;
        foreach ($pts as $c) {
            if ($c['cc'] !== 'ES') {
                $lats[] = $c['lat'];
                $lons[] = $c['lon'];
                $foreign = true;
            }
        }
        foreach ($countries as $c) {
            if (in_array($c['cc'], ['ES', 'ZZ'], true) || isset($located[$c['cc']]) || ! isset($shapes[$c['cc']])) {
                continue;
            }
            [$x0, $y0, $x1, $y1] = $shapes[$c['cc']]['main'];
            array_push($lons, $x0 / $k - 180, $x1 / $k - 180);
            array_push($lats, $top - $y0 / $k, $top - $y1 / $k);
            $foreign = true;
        }
        if (! $foreign) {
            return null;                                   // only Spain (or only hidden): the Spain map says it all
        }

        // the frame, in degrees (longitude already narrowed): padded for dots and
        // names, never closer than the Peninsula, between square-ish and 2:1
        $s = max(.5, cos(deg2rad(max(-60, min(60, (min($lats) + max($lats)) / 2)))));
        $w = (max($lons) - min($lons)) * $s * 1.3 + 4;
        $h = max(11, (max($lats) - min($lats)) * 1.3 + 4);
        $w = max($w, $h * 1.2);
        $h = max($h, $w / 2);
        $w = min($w, 360 * $s);
        $h = min($h, $top - $bottom);
        $left = max(0, min(360 * $s - $w, ((min($lons) + max($lons)) / 2 + 180) * $s - $w / 2));
        $up = max(0, min($top - $bottom - $h, $top - (min($lats) + max($lats)) / 2 - $h / 2));

        $z = self::MAP_W / ($w * $k);
        $H = (int) round($h * $k * $z);
        $xy = fn ($lat, $lon) => [round((($lon + 180) * $s - $left) * $k * $z, 1), round(($top - $lat - $up) * $k * $z, 1)];

        $people = array_column($countries, 'n', 'cc');
        $maxC = max(1, max(array_values(array_diff_key($people, ['ZZ' => 0])) ?: [1]));
        $lands = [];
        foreach ($m['countries'] as $c) {
            [$x0, $y0, $x1, $y1] = $shapes[$c['iso2']]['box'];
            if ($x1 * $s < $left * $k || $x0 * $s > ($left + $w) * $k || $y1 < $up * $k || $y0 > ($up + $h) * $k) {
                continue;                                  // off the frame: not sent
            }
            $n = $people[$c['iso2']] ?? 0;
            $lands[] = ['d' => $c['d'], 'name' => $c['name'], 'n' => $n, 'o' => $n ? round(.25 + .6 * $n / $maxC, 2) : null];
        }

        // one dot per town, merged with a bigger one when they would touch
        $dots = [];
        foreach ($pts as $c) {
            [$x, $y] = $xy($c['lat'], $c['lon']);
            if ($x < 0 || $x > self::MAP_W || $y < 0 || $y > $H) {
                continue;                                  // a Spanish island off the frame
            }
            foreach ($dots as $i => $d) {
                if (hypot($d['x'] - $x, $d['y'] - $y) < 18) {
                    $dots[$i]['n'] += $c['n'];
                    $dots[$i]['towns'][] = $c;
                    continue 2;
                }
            }
            $dots[] = ['x' => $x, 'y' => $y, 'n' => $c['n'], 'towns' => [$c]];
        }
        usort($dots, fn ($a, $b) => $b['n'] <=> $a['n']);
        $max = max(1, $dots[0]['n'] ?? 1);
        $far = $w / $s > 30;                               // a continent or more: foreign dots named by country
        foreach ($dots as &$d) {
            $big = $d['towns'][0];
            $d['r'] = round(4 + 11 * sqrt($d['n'] / $max), 1);
            $d['name'] = $far && $big['cc'] !== 'ES' ? $big['country'] : $big['city'];
            $d['abroad'] = $big['cc'] !== 'ES';
            $more = count($d['towns']) - 1;
            $d['title'] = $big['city'] . ($big['cc'] !== 'ES' && $big['city'] !== $big['country'] ? ' (' . $big['country'] . ')' : '')
                . ($more ? ' y ' . $more . ' ' . ($more === 1 ? 'sitio' : 'sitios') . ' más' : '') . ': ' . $d['n'];
            unset($d['towns']);
        }
        unset($d);

        // names: the places abroad first (they are why this map is here), then Málaga's side
        $order = array_merge(array_filter($dots, fn ($d) => $d['abroad']), array_filter($dots, fn ($d) => ! $d['abroad']));

        return [
            'w'     => self::MAP_W,
            'h'     => $H,
            'm'     => implode(',', array_map(fn ($v) => round($v, 4), [$s * $z, 0, 0, $z, -$left * $k * $z, -$up * $k * $z])),
            'lands' => $lands,
            'dots'  => $dots,
            'names' => self::names(array_slice(array_values($order), 0, 8), self::MAP_W, $H, 4),
        ];
    }

    /**
     * Names beside dots, biggest first, each on the right of its dot (on the left
     * near the right edge), skipped when it would sit on a name already placed
     * or run off the map. 17px type, about 9.5 a letter.
     *
     * @param  list<array{x:float,y:float,r:float,name:string}>  $dots
     * @return list<array{x:float,y:float,end:bool,name:string}>
     */
    private static function names(array $dots, float $w, float $h, int $limit): array
    {
        $placed = [];
        foreach ($dots as $d) {
            if (count($placed) === $limit) {
                break;
            }
            $len = 9.5 * mb_strlen($d['name']);
            $end = $d['x'] + $d['r'] + 5 + $len > $w;
            $x0 = $end ? $d['x'] - $d['r'] - 5 - $len : $d['x'] + $d['r'] + 5;
            $box = [$x0, $d['y'] - 13, $x0 + $len, $d['y'] + 7];
            if ($box[0] < 0 || $box[1] < 0 || $box[3] > $h) {
                continue;
            }
            foreach ($placed as $p) {
                if ($box[0] < $p['box'][2] && $box[2] > $p['box'][0] && $box[1] < $p['box'][3] && $box[3] > $p['box'][1]) {
                    continue 2;
                }
            }
            $placed[] = ['box' => $box, 'x' => round($end ? $d['x'] - $d['r'] - 5 : $d['x'] + $d['r'] + 5, 1),
                'y' => round($d['y'] + 6, 1), 'end' => $end, 'name' => $d['name']];
        }

        return array_map(fn ($p) => array_diff_key($p, ['box' => 0]), $placed);
    }

    /**
     * Each country's extent on the world map: 'box' all of it (to leave out what
     * is off the frame), 'main' its largest piece (to frame on).
     *
     * @return array<string,array{box:array,main:array}>
     */
    private static function shapes(array $m): array
    {
        static $cache = [];
        $key = md5(json_encode(array_column($m['countries'], 'iso2')) . $m['w']);
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        $out = [];
        foreach ($m['countries'] as $c) {
            $all = [INF, INF, -INF, -INF];
            $main = null;
            $area = -1;
            foreach (self::rings($c['d']) as $ring) {
                $b = [INF, INF, -INF, -INF];
                $a = 0;
                foreach ($ring as $i => [$x, $y]) {
                    $b = [min($b[0], $x), min($b[1], $y), max($b[2], $x), max($b[3], $y)];
                    [$nx, $ny] = $ring[($i + 1) % count($ring)];
                    $a += $x * $ny - $nx * $y;
                }
                $all = [min($all[0], $b[0]), min($all[1], $b[1]), max($all[2], $b[2]), max($all[3], $b[3])];
                if (abs($a) > $area) {
                    $area = abs($a);
                    $main = $b;
                }
            }
            if ($main) {
                $out[$c['iso2']] = ['box' => $all, 'main' => $main];
            }
        }

        return $cache[$key] = $out;
    }

    /** The rings of an "M x,y L x,y x,y … Z" path, as lists of [x, y]. */
    private static function rings(string $d): array
    {
        $rings = [];
        foreach (preg_split('~M~', $d, -1, PREG_SPLIT_NO_EMPTY) as $part) {
            preg_match_all('~(-?[\d.]+),(-?[\d.]+)~', $part, $mm, PREG_SET_ORDER);
            if (count($mm) >= 3) {
                $rings[] = array_map(fn ($p) => [(float) $p[1], (float) $p[2]], $mm);
            }
        }

        return $rings;
    }

    /**
     * The Spanish region a point falls in, read off the map: for a region name
     * DB-IP spells in a way the aliases do not know. Even-odd across the rings
     * (so a hole is a hole); a point just off a simplified coast goes to the
     * nearest region within ~30 km.
     */
    private static function esRegionAt(float $lat, float $lon, array $m): ?string
    {
        static $cache = [];
        $key = md5($m['regions'][0]['d'] ?? '');
        $cache[$key] ??= array_map(fn ($r) => ['id' => $r['id'], 'rings' => self::rings($r['d'])], $m['regions']);
        [$x, $y] = self::esXy($lat, $lon, $m);
        $best = null;
        $bestD = 12.0;
        foreach ($cache[$key] as $r) {
            $in = false;
            foreach ($r['rings'] as $ring) {
                $j = count($ring) - 1;
                foreach ($ring as $i => [$xi, $yi]) {
                    [$xj, $yj] = $ring[$j];
                    if (($yi > $y) !== ($yj > $y) && $x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi) {
                        $in = ! $in;
                    }
                    $j = $i;
                    $dd = hypot($xi - $x, $yi - $y);
                    if ($dd < $bestD) {
                        $bestD = $dd;
                        $best = $r['id'];
                    }
                }
            }
            if ($in) {
                return $r['id'];
            }
        }

        return $best;
    }

    /** x, y on the Spain map (same formulas as resources/data/map-es.json was drawn with). */
    public static function esXy(float $lat, float $lon, array $m): array
    {
        $x = ($lon - $m['lon0']) * $m['k'];
        $y = (self::merc($m['latTop']) - self::merc($lat)) * $m['k'];
        if ($lon < -13 && $lat < 30) {
            $x += $m['canary']['dx'];
            $y += $m['canary']['dy'];
        }

        return [round($x, 1), round($y, 1)];
    }

    public static function worldXy(float $lat, float $lon, array $m): array
    {
        return [round(($lon + 180) * $m['k'], 1), round(($m['latTop'] - $lat) * $m['k'], 1)];
    }

    private static function merc(float $lat): float
    {
        return log(tan(M_PI / 4 + deg2rad($lat) / 2)) * 180 / M_PI;
    }

    private static function km(float $a, float $b, float $c, float $d): float
    {
        $p = M_PI / 180;
        $h = sin(($c - $a) * $p / 2) ** 2 + cos($a * $p) * cos($c * $p) * sin(($d - $b) * $p / 2) ** 2;

        return 12742 * asin(min(1, sqrt($h)));
    }

    /** @return array the map geometry (resources/data/map-{es,world}.json), [] when missing */
    public static function map(string $which): array
    {
        static $cache = [];
        if (! isset($cache[$which])) {
            $f = resource_path('data/map-' . $which . '.json');
            $cache[$which] = is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
        }

        return $cache[$which];
    }

    /* ---- photographs ------------------------------------------------------ */

    /**
     * How far people get into a car's photographs (position 1, 2, 3… across all
     * cars, as a share of the measured page views of cars that have that many),
     * and how the seconds split between exterior, interior and flaws.
     *
     * @return array{views:int,avg:?float,secs:?int,reach:list<array>,groups:list<array>}
     */
    public static function photos(array $by): array
    {
        $views = $seen = $secs = $seenN = 0;
        $reach = [];
        $groups = [];
        $cars = Vehicle::query()->get(['slug', 'cover_image', 'gallery_images', 'image_tags']);
        foreach ($cars as $car) {
            $timen = (int) ($by['timen']['/coche/' . $car->slug] ?? 0);
            if (! $timen) {
                continue;
            }
            $views += $timen;
            foreach (self::photoList($car) as $i => $p) {
                $id = $car->slug . '|' . $p['k'];
                $n = (int) ($by['pic'][$id] ?? 0);
                $s = (int) ($by['pict'][$id] ?? 0);
                $seen += $n;
                $secs += $s;
                $seenN += $n;
                if ($i < 40) {
                    $reach[$i] ??= ['n' => 0, 'of' => 0];
                    $reach[$i]['n'] += $n;
                    $reach[$i]['of'] += $timen;
                }
                $g = $p['group'] ?: 'none';
                $groups[$g] = ($groups[$g] ?? 0) + $s;
            }
        }
        $label = ['exterior' => 'Exterior', 'interior' => 'Interior', 'flaw' => 'Imperfecciones', 'none' => 'Sin clasificar'];
        $gsum = max(1, array_sum($groups));
        arsort($groups);

        return [
            'views'  => $views,
            'avg'    => $views ? round($seen / $views, 1) : null,
            'secs'   => $seenN ? (int) round($secs / $seenN) : null,
            'reach'  => array_map(fn ($i, $r) => ['pos' => $i + 1, 'pct' => $r['of'] ? (int) round(100 * $r['n'] / $r['of']) : 0, 'of' => $r['of']],
                array_keys($reach), $reach),
            'groups' => array_map(fn ($g, $s) => ['label' => $label[$g] ?? $g, 'n' => $s, 'share' => (int) round(100 * $s / $gsum)],
                array_keys(array_filter($groups)), array_filter($groups)),
        ];
    }

    /**
     * One car's photographs in the page's "Todas" order, each with the page views
     * that saw it, the seconds on it, and full-screen opens, over $from…$to.
     *
     * @return array{views:int,list:list<array>}
     */
    public static function carPhotos(Vehicle $car, string $from, string $to): array
    {
        $rows = DB::table('stats')->whereBetween('day', [$from, $to])
            ->whereIn('metric', ['pic', 'pict', 'picz'])
            ->where('key', '>', $car->slug . '|')->where('key', '<', $car->slug . '}')
            ->groupBy('metric', 'key')->select('metric', 'key', DB::raw('sum(n) as n'))->get();
        $m = [];
        foreach ($rows as $r) {
            $m[$r->metric][substr($r->key, strlen($car->slug) + 1)] = (int) $r->n;
        }
        $views = (int) DB::table('stats')->whereBetween('day', [$from, $to])->where('metric', 'timen')
            ->where('key', '/coche/' . $car->slug)->sum('n');

        $list = [];
        foreach (self::photoList($car) as $p) {
            $seen = $m['pic'][$p['k']] ?? 0;
            $sec = $m['pict'][$p['k']] ?? 0;
            $list[] = $p + [
                'seen' => $seen,
                'pct'  => $views ? min(100, (int) round(100 * $seen / $views)) : null,
                'sec'  => $seen ? (int) round($sec / $seen) : null,
                'big'  => $m['picz'][$p['k']] ?? 0,
            ];
        }

        return ['views' => $views, 'list' => $list];
    }

    /**
     * A car's photographs as its page lists them under "Todas": the cover, then
     * the gallery, each once (CarPageController::show does the same).
     *
     * @return list<array{path:string,k:string,group:?string,n:int}>
     */
    public static function photoList(Vehicle $car): array
    {
        $all = array_values(array_unique(array_filter(array_merge(
            [$car->cover_image],
            is_array($car->gallery_images) ? $car->gallery_images : []
        ))));
        $tags = is_array($car->image_tags) ? $car->image_tags : [];
        $out = [];
        foreach ($all as $i => $path) {
            $out[] = ['path' => $path, 'k' => Stats::photoKey($path), 'group' => $tags[$path] ?? null, 'n' => $i + 1];
        }

        return $out;
    }

    /* ---- before the browser counted -------------------------------------- */

    /**
     * The days estimated from the server's log (metrics "h.*", stats:history),
     * and every car ever sold, by marque.
     *
     * @return array{days:list<array>,from:?string,to:?string,people:int,views:int,cars:list<array>,sources:array,places:array,sold:list<array>}
     */
    public static function history(): array
    {
        $rows = DB::table('stats')->where('metric', 'like', 'h.%')
            ->groupBy('metric', 'key')->select('metric', 'key', DB::raw('sum(n) as n'))->get();
        $by = [];
        foreach ($rows as $r) {
            $by[substr($r->metric, 2)][$r->key] = (int) $r->n;
        }
        $daily = DB::table('stats')->where('metric', 'h.people')->where('key', '')->orderBy('day')->pluck('n', 'day')->all();

        $names = Vehicle::whereIn('slug', array_keys($by['carp'] ?? []))->get(['slug', 'brand', 'model', 'year', 'status'])
            ->keyBy('slug');
        $cars = [];
        foreach ($by['carp'] ?? [] as $slug => $n) {
            $c = $names[$slug] ?? null;
            if (! $c) {
                continue;
            }
            $cars[] = ['slug' => $slug, 'name' => trim($c->brand . ' ' . $c->model . ' ' . $c->year), 'people' => $n,
                'views' => (int) ($by['car'][$slug] ?? 0), 'sold' => $c->status === 'sold'];
        }
        usort($cars, fn ($a, $b) => $b['people'] <=> $a['people']);

        $sold = [];
        foreach (Vehicle::where('status', 'sold')->get(['brand', 'price']) as $c) {
            [$k, $name] = self::marque($c->brand);
            $sold[$k] ??= ['name' => $name, 'n' => 0, 'sum' => 0];
            $sold[$k]['n']++;
            $sold[$k]['sum'] += (int) $c->price;
        }
        uasort($sold, fn ($a, $b) => $b['n'] <=> $a['n']);

        return [
            'days'    => array_map(fn ($d, $n) => ['day' => $d, 'n' => (int) $n], array_keys($daily), $daily),
            'from'    => array_key_first($daily),
            'to'      => array_key_last($daily),
            'people'  => array_sum($by['people'] ?? []),
            'views'   => array_sum($by['views'] ?? []),
            'cars'    => $cars,
            'sources' => $by['source'] ?? [],
            'search'  => $by['search'] ?? [],
            'places'  => self::places($by['geo'] ?? []),
            'sold'    => array_values(array_map(fn ($s) => $s + ['avg' => (int) round($s['sum'] / max(1, $s['n']))], $sold)),
        ];
    }
}
