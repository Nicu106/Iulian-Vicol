<?php

namespace App\Support;

use App\Models\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * The fortnight before the browser counted (24.10, 18:03): an ESTIMATE from
 * nginx's access log, kept apart from the real counters as "h.*" metrics
 * (h.people, h.views, h.visits, h.car, h.carp, h.page, h.source, h.device,
 * h.geo, h.search) and shown on Estadísticas under its own heading.
 *
 * Who is counted. In the log 90 % of page requests are robots that say they are
 * browsers. A real browser shows itself by what it asks for AFTER the page,
 * with the page as referrer: /manifest.json (every page links it), /og/… and
 * /img/… — which robots that read only the HTML never do. So: a person is an
 * IP + browser that made such a request, is not a robot by its name
 * (Stats::isBot), is not this server or another server farm (Geo::hosting —
 * Meta's and Google's crawlers render pages too), and is not the team: any IP
 * that ever opened /admin, /login, /brandbook or /muestras in the log is the
 * owner's line for the whole period, and so is, outside Spain, the country he
 * signed in from (his phone's data there has other addresses). Then the same rules as the beacon: a day per person, a
 * reload within 10 s is not a view, 30 minutes of silence end a visit.
 *
 * What it cannot see: WhatsApp and phone presses (they leave the site without a
 * request), time and photographs (no script), and visitors whose browser had
 * everything cached and asked for nothing after the page — so it undercounts a
 * little, never overcounts robots.
 */
final class StatsHistory
{
    /** When the beacon took over: the log after this is not read. */
    public const UNTIL = '2026-10-24 18:03:21';

    private const LINE = '~^(\S+) \S+ \S+ \[([^\]]+)\] "(\S+) (\S+) [^"]*" (\d{3}) \S+ "([^"]*)" "([^"]*)"~';

    /**
     * @param  list<string>  $files  access logs, plain or .gz
     * @return array{days:int,people:int,views:int}
     */
    public static function import(array $files, string $host = 'ivmotorclass.com'): array
    {
        $until = CarbonImmutable::parse(self::UNTIL, Stats::TZ)->getTimestamp();
        $own = array_filter([gethostbyname($host), '127.0.0.1', '::1']);
        $slugs = array_flip(Vehicle::pluck('slug')->all());

        $hits = [];
        $render = [];
        $team = [];
        foreach ($files as $f) {
            $h = str_ends_with($f, '.gz') ? @gzopen($f, 'r') : @fopen($f, 'r');
            if (! $h) {
                continue;
            }
            while (($l = str_ends_with($f, '.gz') ? gzgets($h) : fgets($h)) !== false) {
                if (! preg_match(self::LINE, $l, $m)) {
                    continue;
                }
                [, $ip, $ts, $method, $url, $status, $ref, $ua] = $m;
                $t = \DateTimeImmutable::createFromFormat('d/M/Y:H:i:s O', $ts);
                if (! $t || $t->getTimestamp() >= $until) {
                    continue;
                }
                $path = (string) parse_url($url, PHP_URL_PATH);
                if (preg_match('~^/(admin|login|brandbook|muestras)(/|$)~', $path)) {
                    $team[$ip] = true;
                    continue;
                }
                if (preg_match('~^/(manifest\.json$|og/|img/)~', $path) && str_contains($ref, $host)) {
                    $render[$ip . '|' . $ua] = true;
                    continue;
                }
                if ($method !== 'GET' || $status !== '200') {
                    continue;
                }
                $place = self::page($path, $slugs);
                if ($place) {
                    $hits[] = [$t->getTimestamp(), $ip, $ua, $place[0], $place[1], (string) parse_url($url, PHP_URL_QUERY), $ref];
                }
            }
            str_ends_with($f, '.gz') ? gzclose($h) : fclose($h);
        }
        usort($hits, fn ($a, $b) => $a[0] <=> $b[0]);

        // The owner abroad uses more lines than the ones he signed in from (his
        // phone's data, a university's network). Outside Spain, a country the team
        // signed in from is the team's for this estimate. Spain never is.
        $teamCountries = [];
        foreach (array_keys($team) as $ip) {
            $g = Geo::find((string) $ip);
            if ($g && $g['cc'] !== 'ES' && $g['cc'] !== 'ZZ') {
                $teamCountries[$g['cc']] = true;
            }
        }
        $country = [];

        $inc = [];
        $people = [];
        $last = [];
        $cars = [];
        foreach ($hits as [$t, $ip, $ua, $path, $slug, $query, $ref]) {
            $who = $ip . '|' . $ua;
            if (! isset($render[$who]) || isset($team[$ip]) || in_array($ip, $own, true) || Stats::isBot($ua)) {
                continue;
            }
            $country[$ip] ??= (Geo::find($ip)['cc'] ?? '') . (Geo::hosting($ip) ? '!' : '');
            if (str_ends_with($country[$ip], '!') || isset($teamCountries[$country[$ip]])) {
                continue;                                   // a server farm, or the owner's country abroad
            }
            $day = CarbonImmutable::createFromTimestamp($t, Stats::TZ)->toDateString();
            $pid = $day . '|' . $who;
            $prev = $last[$pid] ?? null;
            if ($prev && $prev[0] === $path && $t - $prev[1] < 10) {
                continue;
            }
            $add = function (string $metric, string $key = '', int $n = 1) use (&$inc, $day) {
                $id = $day . "\0" . $metric . "\0" . $key;
                $inc[$id] = ($inc[$id] ?? 0) + $n;
            };
            $add('views');
            if (! isset($people[$pid])) {
                $people[$pid] = true;
                $add('people');
                $add('device', Journey::describe($ua, '')['device']);
                if ($g = Geo::find($ip)) {
                    $add('geo', Geo::key($g));
                }
            }
            if (! $prev || $t - $prev[1] > 30 * 60) {
                $add('visits');
                $add('source', Stats::source($ref === '-' ? '' : $ref, $ua, $host));
            }
            $last[$pid] = [$path, $t];
            if ($slug !== null) {
                $add('car', $slug);
                if (! isset($cars[$pid][$slug])) {
                    $cars[$pid][$slug] = true;
                    $add('carp', $slug);
                }
            } else {
                $add('page', $path);
            }
            if ($path === '/catalogo') {
                parse_str($query, $q);
                foreach (Stats::searches(array_filter($q, 'is_string')) as $k) {
                    $add('search', $k);
                }
            }
        }

        $rows = [];
        foreach ($inc as $id => $n) {
            [$day, $metric, $key] = explode("\0", $id, 3);
            $rows[] = ['day' => $day, 'metric' => 'h.' . $metric, 'key' => mb_substr($key, 0, 160), 'n' => $n];
        }
        DB::transaction(function () use ($rows) {
            DB::table('stats')->where('metric', 'like', 'h.%')->delete();
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('stats')->insert($chunk);
            }
        });

        $days = array_unique(array_map(fn ($r) => $r['day'], $rows));

        return ['days' => count($days), 'people' => count($people),
            'views' => array_sum(array_map(fn ($r) => $r['metric'] === 'h.views' ? $r['n'] : 0, $rows))];
    }

    /** @return array{0:string,1:?string}|null the page and car, as Stats::place has them (the old /vehicles/ address too) */
    private static function page(string $path, array $slugs): ?array
    {
        $path = '/' . trim($path, '/');
        if (isset(Stats::PAGES[$path])) {
            return [$path, null];
        }
        if (preg_match('~^/(?:coche|vehicles)/([a-z0-9-]{1,120})$~', $path, $m) && isset($slugs[$m[1]])) {
            return ['/coche/' . $m[1], $m[1]];
        }

        return null;
    }
}
