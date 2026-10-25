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
                $rid = $alias[$g['region']] ?? null;
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

        return [
            'total'     => $total,
            'near'      => $near,
            'andalucia' => $andalucia,
            'spain'     => $spain,
            'abroad'    => $total - $spain,
            'cities'    => $cities,
            'regions'   => $regions,
            'names'     => $names,
            'countries' => array_map(fn ($cc, $n) => ['cc' => $cc, 'name' => Geo::country($cc), 'n' => $n], array_keys($countries), $countries),
            'es'        => $meta ?: null,
            'world'     => self::map('world') ?: null,
        ];
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
