<?php

namespace App\Support;

use App\Http\Controllers\SellCarController;
use App\Models\ContactMessage;
use App\Models\Referrer;
use App\Models\Testimonial;
use App\Models\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * What people do on the site, counted for the panel's Estadísticas.
 *
 * HOW IT IS COUNTED. The server logs cannot do this: on 22.10 the page requests
 * in them were 90 % robots (security scanners, a monitor every two minutes,
 * Google) and the rest could not be told apart, so a log count said "100 people
 * a day" where there are perhaps ten. Instead each page sends one small beacon
 * from the browser (layouts/site.blade.php → POST /s). Scanners do not run
 * JavaScript; what is left of the robots is caught by Journey::isBot.
 *
 * WHAT IS KEPT, and what is not:
 *   - no cookie, no localStorage, no IP. A person is a hash of a salt that
 *     changes every day, the IP and the browser (stat_seen). It tells the second
 *     page from a new person; after two days the row and the salt are gone, so
 *     nobody can be followed from one day to the next. A person who comes back
 *     another day counts again — every cookieless counter has this limit.
 *   - per day, counters only (stats): day + metric + key → n.
 *   - per visit (stat_visits, 30 days): the device as the browser announces it,
 *     where it came from, and the pages and presses in order, with times.
 *     Never what anyone typed, never a name or a number.
 *   - the owner and his team are not counted, signed in or not: a device that has
 *     ever been signed in to the panel (or opened the "no contar" link) carries
 *     the mc_staff cookie, and the connection it used is theirs for 6 hours
 *     (stat_staff) — which also takes out, number by number, whatever that
 *     connection was counted for earlier the same day (forget). Nor the server
 *     itself, robots, or server farms posing as browsers (Geo::hosting).
 *   - one beacon at a time (locked()), and the day's salt in the database
 *     (stat_salt), so a burst of beacons or a cache:clear never splits a person.
 *
 * METRICS (stats.metric → key):
 *   people, visits, views     ''        a person / a visit (30 min of silence ends one) / a page
 *   device                    mobile|desktop|tablet, once per person a day
 *   source                    google|instagram|…|cartel|directo, once per visit
 *   page                      '/catalogo' …     views of a page that is not a car
 *   car, carp, qr             slug              a car's views / people / scans of its bonnet placard
 *   carsrc                    'slug|source'     where the people who saw a car came from
 *   photos                    slug              photographs seen, summed (÷ timen = per view)
 *   wa, tel, mail             path              people who pressed WhatsApp, the phone, e-mail there (once a day each)
 *   wap                       ''                people who pressed WhatsApp anywhere (for "1 de cada N")
 *   ev                        'name|path'       people who pressed data-stat="name" there; forms' start/send
 *   evk                       'name|k'          the same with what it was about: 'marca-abrir|bmw', 'avisame|audi', 'wa|bmw'
 *   time, timen               path              seconds visible, summed / page views measured
 *   s75, s100                 path              page views that reached 75 % / the end
 *   photos                    slug              photographs seen (0.7 s or more), summed over page views
 *   pic, pict, picz           'slug|k'          one photograph: page views that saw it / seconds on it / opened big
 *   geo                       'ES|Andalusia|Málaga|36.72|-4.42'  the town of each person (App\Support\Geo)
 *   cargeo                    'slug|ES|Málaga'  the town of the people who saw a car
 *   search                    'marca:bmw' 'modelo:…' 'max:20000' 'cuota:300', once per person and day
 *   h.*                       as above          the fortnight before 24.10, ESTIMATED from the server log
 *                                               (App\Support\StatsHistory) — kept apart, never summed with these
 *
 * WHERE THE PRESSES COME FROM (layouts/site.blade.php): links to WhatsApp, tel:,
 * mailto: and Google Maps are recognised by their address; anything else is
 * marked in the page with data-stat="name" (EVENTS below), a viewer that opens
 * with data-stat-open="name", a form with data-stat-form="name" (→ name-empieza
 * on first focus, name-envia on submit). The car page reports the photographs
 * seen through window.mcStatPhoto. The bonnet placard's QR opens ?c=qr.
 *
 * WHERE IT SHOWS: /admin/estadisticas (Admin\StatsController; the Panel's
 * "Ver estadísticas" opens the same in a popup), ?coche={slug} for one car, a
 * line on every car in the Panel and Coches lists (perCar), and the "En la web"
 * box on a car's edit page (car()).
 */
final class Stats
{
    public const TZ = 'Europe/Madrid';

    public const PERIODS = [7, 30, 90];

    /** Past this much silence, the next page starts a new visit. */
    private const VISIT_GAP = 30 * 60;

    /** The same page again within this many seconds is a reload, not a view. */
    private const RELOAD = 10;

    /** "Ahora en la web": seen in the last five minutes. */
    private const NOW = 5 * 60;

    private const KEEP_VISITS = 30;

    private const MAX_STEPS = 40;

    /** A tab left open all afternoon is not half an hour of reading. */
    public const MAX_SECONDS = 30 * 60;

    private const JSON = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    public const PAGES = [
        '/'                 => 'Inicio',
        '/catalogo'         => 'Coches',
        '/por-que-nosotros' => 'Por qué nosotros',
        '/contacto'         => 'Contacto',
        '/vende'            => 'Vende tu coche',
        '/recomienda'       => 'Recomienda',
    ];

    public const SOURCES = [
        'google'      => 'Google',
        'buscadores'  => 'Otros buscadores',
        'instagram'   => 'Instagram',
        'facebook'    => 'Facebook',
        'whatsapp'    => 'WhatsApp',
        'tiktok'      => 'TikTok',
        'youtube'     => 'YouTube',
        'cochesnet'   => 'coches.net',
        'wallapop'    => 'Wallapop',
        'milanuncios' => 'Milanuncios',
        'ia'          => 'Asistentes de IA (ChatGPT…)',
        'cartel'      => 'El cartel del coche (QR)',
        'otras'       => 'Otras webs',
        'directo'     => 'Directo',
    ];

    public const DEVICES = ['mobile' => 'Móvil', 'desktop' => 'Ordenador', 'tablet' => 'Tableta'];

    /** data-stat names → [what the panel says, the short word in a visit]. */
    public const EVENTS = [
        'galeria'            => ['Abrieron las fotos a pantalla completa', 'abrió las fotos'],
        'fotos-tipo'         => ['Filtraron las fotos (exterior, interior…)', 'filtró las fotos'],
        'video'              => ['Pusieron el vídeo', 'puso el vídeo'],
        'compartir'          => ['Compartieron un coche', 'compartió'],
        'extras'             => ['Abrieron la lista de extras', 'miró los extras'],
        'descripcion'        => ['Leyeron la descripción entera', 'leyó la descripción'],
        'marca-abrir'        => ['Abrieron todos los coches de una marca', 'abrió una marca'],
        'buscar'             => ['Buscaron desde la portada', 'buscó'],
        'mapa'               => ['Abrieron el mapa', 'abrió el mapa'],
        'contacto-empieza'   => ['Empezaron el formulario de contacto', 'empezó el formulario'],
        'contacto-envia'     => ['Enviaron el formulario de contacto', 'envió el formulario'],
        'vende-empieza'      => ['Empezaron «Vende tu coche»', 'empezó «Vende»'],
        'vende-envia'        => ['Pulsaron Enviar en «Vende tu coche»', 'pulsó Enviar'],
        'recomienda-empieza' => ['Empezaron a pedir su enlace de recomendación', 'empezó «Recomienda»'],
        'recomienda-envia'   => ['Pulsaron «Crear mi enlace»', 'pulsó «Crear mi enlace»'],
    ];

    public static function clock(): CarbonImmutable
    {
        return CarbonImmutable::now(self::TZ);
    }

    public static function today(): string
    {
        return self::clock()->toDateString();
    }

    /* =====================================================================
       Writing
       ===================================================================== */

    /**
     * One beacon. Returns whether anything was counted (the endpoint answers
     * 204 either way, so a robot learns nothing from it).
     *
     * One beacon at a time (locked()): two pages opening at once used to read
     * "not seen today" twice and count one person as two.
     */
    public static function hit(Request $request): bool
    {
        $who = self::audience($request);
        if ($who === '') {
            return false;
        }

        $place = self::place((string) $request->input('p'));
        if (! $place) {
            return false;
        }
        [$path, $slug] = $place;

        $type = (string) $request->input('t');

        // The team's beacons go the same way, written as their own visits
        // (team = 1) and into no counter: "Tus visitas" shows them.
        self::$team = $who === 'team';
        try {
            $done = self::locked(fn () => match (true) {
                $type === 'view'                             => self::view($request, $path, $slug),
                $type === 'eng'                              => self::engage($request, $path, $slug),
                in_array($type, ['wa', 'tel', 'mail'], true) => self::press($request, $path, $type, $path, self::k($request)),
                $type === 'ev'                               => self::event($request, $path),
                default                                      => false,
            });
        } finally {
            self::$team = false;
        }

        return $done && $who === 'visitor';
    }

    /** While a team beacon is written: no counters, its own rows. */
    private static bool $team = false;

    public static function allowed(Request $request): bool
    {
        return self::audience($request) === 'visitor';
    }

    /**
     * Who is counted. Ignored: robots and server farms, the server itself, and
     * anything not sent by this site's own pages. The team — signed in, or on a
     * device that has ever been signed in or opened the "no contar" link, or on
     * a connection one of those used in the last hours — is recorded as its own
     * visits ("Tus visitas" in the panel) and never counted.
     *
     * @return string 'visitor' (counted), 'team' (recorded, never counted) or '' (ignored)
     */
    public static function audience(Request $request): string
    {
        if (self::isBot((string) $request->userAgent())) {
            return '';
        }
        $ip = (string) $request->ip();
        if ($ip === '' || $ip === '::1' || str_starts_with($ip, '127.')) {
            return '';
        }
        $site = $request->header('Sec-Fetch-Site');
        if ($site !== null && $site !== 'same-origin') {
            return '';
        }
        if (Auth::check() || self::staffDevice($request)) {
            self::markStaff($ip);

            return 'team';
        }
        if (self::staffIp($ip)) {
            return 'team';
        }

        return Geo::hosting($ip) ? '' : 'visitor';
    }

    /**
     * Robots that run JavaScript. The beacon needs a browser, so what is left
     * after Journey's list is the rendering crawlers, audit tools and automated
     * browsers (the page itself also stays silent when navigator.webdriver is set).
     */
    public static function isBot(string $ua): bool
    {
        return Journey::isBot($ua)
            || ! str_starts_with($ua, 'Mozilla/')
            || (bool) preg_match('~google-|googleother|inspectiontool|mediapartners|apis-google|feedfetcher|pingdom|gtmetrix|ptst|siteimprove|ahrefs|semrush|mj12|dataprovider|uptime|monitor|scan|prerender|phantomjs|selenium|puppeteer|playwright|cypress|chatgpt|claude|anthropic|perplexity|ccbot|amazonbot|applebot|meta-external|bytespider|petalbot~i', $ua);
    }

    /**
     * A page of this site, or null. Cars must exist: a made-up slug is not
     * given a row.
     *
     * @return array{0:string,1:?string}|null [path, car slug]
     */
    public static function place(string $p): ?array
    {
        $path = '/' . trim((string) parse_url($p, PHP_URL_PATH), '/');
        if (isset(self::PAGES[$path])) {
            return [$path, null];
        }
        if (preg_match('~^/coche/([a-z0-9-]{1,120})$~', $path, $m) && Vehicle::where('slug', $m[1])->exists()) {
            return [$path, $m[1]];
        }

        return null;
    }

    /* ---- the owner and his team ------------------------------------------- */

    /** The cookie on a device of the team. Set by the panel (MarkStatsStaff) and by the "no contar" link. */
    public const STAFF_COOKIE = 'mc_staff';

    /** How long a connection the team used stays theirs. */
    public const STAFF_IP = 6 * 3600;

    /** Five years, HttpOnly: the browser keeps it, no script reads it. */
    public static function staffCookie(): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(self::STAFF_COOKIE, '1', 60 * 24 * 365 * 5, '/', null, null, true, false, 'lax');
    }

    public static function staffDevice(Request $request): bool
    {
        return $request->cookie(self::STAFF_COOKIE) === '1';
    }

    public static function staffIp(string $ip): bool
    {
        return DB::table('stat_staff')->where('ip', self::connection($ip))->where('until', '>=', self::clock()->getTimestamp())->exists();
    }

    /**
     * This connection is the team's for the next hours. The first time (or after
     * it lapsed), whatever its browsers were counted for today and yesterday is
     * taken out again: the owner who looks at the site and THEN signs in.
     */
    public static function markStaff(string $ip): void
    {
        if ($ip === '' || $ip === '::1' || str_starts_with($ip, '127.')) {
            return;
        }
        $key = self::connection($ip);
        $now = self::clock()->getTimestamp();
        $until = DB::table('stat_staff')->where('ip', $key)->value('until');
        if ($until !== null && (int) $until - $now > self::STAFF_IP - 600) {
            return;                                     // refreshed in the last ten minutes
        }
        DB::table('stat_staff')->upsert([['ip' => $key, 'until' => $now + self::STAFF_IP]], ['ip'], ['until']);
        if ($until === null || (int) $until < $now) {
            self::locked(fn () => self::forget($ip) >= 0);
        }
    }

    /** Takes today's and yesterday's people on this connection out of every number. */
    public static function forget(string $ip): int
    {
        $now = self::clock();
        $n = 0;
        foreach ([$now->toDateString(), $now->subDay()->toDateString()] as $day) {
            $rows = DB::table('stat_seen')->where('day', $day)->where('iph', self::iph($day, $ip))->get();
            foreach ($rows as $r) {
                self::undo($r);
                $n++;
            }
        }

        return $n;
    }

    /** One person-day out: their counters subtracted, their visits deleted. */
    private static function undo(object $r): void
    {
        $done = json_decode((string) $r->inc, true) ?: [];
        $visits = array_map('intval', (array) ($done['_v'] ?? []));
        unset($done['_v']);
        foreach ($done as $id => $n) {
            [$metric, $key] = explode("\t", (string) $id, 2) + [1 => ''];
            DB::table('stats')->where('day', $r->day)->where('metric', $metric)->where('key', $key)
                ->update(['n' => DB::raw('max(0, "n" - ' . (int) $n . ')')]);
        }
        DB::table('stats')->where('day', $r->day)->where('n', '<=', 0)->delete();
        if ($visits) {
            DB::table('stat_visits')->whereIn('id', $visits)->update(['team' => true]);
        }
        DB::table('stat_seen')->where('day', $r->day)->where('hash', $r->hash)->delete();
    }

    /** A connection, remembered for hours without keeping the address itself. */
    public static function connection(string $ip): string
    {
        return substr(hash_hmac('sha256', $ip, (string) config('app.key')), 0, 32);
    }

    /* ---- the beacons --------------------------------------------------------- */

    private static function view(Request $request, string $path, ?string $slug): bool
    {
        $now = self::clock();
        $day = $now->toDateString();
        $t = $now->getTimestamp();
        $ip = (string) $request->ip();
        $ua = (string) $request->userAgent();
        $hash = self::hash($day, $ip, $ua);
        $query = self::query((string) $request->input('q'));
        $qr = $slug !== null && ($query['c'] ?? '') === 'qr';

        $seen = DB::table('stat_seen')->where('day', $day)->where('hash', $hash)->first();
        if ($seen && $seen->last_path === $path && $t - (int) $seen->last_at < self::RELOAD) {
            return false;
        }
        $done = $seen ? self::done($seen) : [];

        $inc = [['views', '']];
        $source = $seen->source ?? '';
        $visit = $seen->visit ?? null;

        $who = Journey::describe($ua, '');
        $geo = null;
        if (! $seen) {
            $geo = Geo::find($ip);
            $inc[] = ['people', ''];
            $inc[] = ['device', $who['device']];
            if ($geo) {
                $inc[] = ['geo', Geo::key($geo)];
            }
        }

        if (! $seen || ! $visit || $t - (int) $seen->last_at > self::VISIT_GAP) {
            $geo ??= Geo::find($ip);
            $source = $qr ? 'cartel' : self::source((string) $request->input('r'), $ua, $request->getHost());
            $inc[] = ['visits', ''];
            $inc[] = ['source', $source];
            $visit = DB::table('stat_visits')->insertGetId([
                'day'        => $day,
                'started_at' => $t,
                'last_at'    => $t,
                'device'     => $who['device'],
                'os'         => $who['os'],
                'browser'    => $who['browser'],
                'source'     => $source,
                'landing'    => $path,
                'steps'      => json_encode([['p' => $path, 't' => 0]], self::JSON),
                'pages'      => 1,
                'place'      => Geo::place($geo),
                'team'       => self::$team,
            ]);
            $done['_v'][] = $visit;
        } else {
            self::step((int) $visit, $t, fn (array $steps) => count($steps) < self::MAX_STEPS
                ? [...$steps, ['p' => $path, 't' => null]] : $steps, true);
        }

        $cars = ($seen && $seen->cars) ? explode(',', $seen->cars) : [];
        if ($slug !== null) {
            $inc[] = ['car', $slug];
            if ($qr) {
                $inc[] = ['qr', $slug];
            }
            if (! in_array($slug, $cars, true)) {
                $cars[] = $slug;
                $inc[] = ['carp', $slug];
                $inc[] = ['carsrc', $slug . '|' . $source];
                // and where the people who saw it are
                $g = $geo ?? Geo::parse(self::doneGeo($done));
                if ($g) {
                    $inc[] = ['cargeo', $slug . '|' . $g['cc'] . '|' . $g['city']];
                }
            }
        } else {
            $inc[] = ['page', $path];
        }

        // What was asked for, once per person and day: one person searching
        // "BMW" five times is one person who wants a BMW.
        if ($path === '/catalogo') {
            foreach (self::searches($query) as $k) {
                if (! isset($done["search\t" . $k])) {
                    $inc[] = ['search', $k];
                }
            }
        }

        $done = self::add($done, $inc);
        DB::table('stat_seen')->upsert([[
            'day'       => $day,
            'hash'      => $hash,
            'iph'       => self::$team ? '' : self::iph($day, $ip),     // forget() never touches the team's own rows
            'last_at'   => $t,
            'last_path' => $path,
            'visit'     => $visit,
            'source'    => $source,
            'cars'      => implode(',', array_slice($cars, -150)),
            'inc'       => json_encode($done, self::JSON),
            'team'      => self::$team,
        ]], ['day', 'hash'], ['iph', 'last_at', 'last_path', 'visit', 'source', 'cars', 'inc', 'team']);

        self::bump($day, $inc);
        self::sometimesPrune();

        return true;
    }

    /**
     * Time visible, how far down, the photographs — sent when the page is left
     * or hidden, as differences since the last report.
     *
     * pv  photographs seen (on screen for 0.7 s or more), each once per page view
     * pt  "k:seconds,…" seconds each photograph was on screen since the last report
     * pz  photographs opened full screen, each once per page view
     * A photograph is its data-k: the first 8 hex of md5(its stored path).
     */
    private static function engage(Request $request, string $path, ?string $slug): bool
    {
        [$seen, $day, $t] = self::seen($request);
        if (! $seen) {
            return false;
        }

        $s = max(0, min(self::MAX_SECONDS, (int) $request->input('s')));
        $y = self::bucket((int) $request->input('y'));
        $y0 = self::bucket((int) $request->input('y0'));

        $inc = [];
        if ($request->input('f') === '1') {
            $inc[] = ['timen', $path];
        }
        if ($s > 0) {
            $inc[] = ['time', $path, $s];
        }
        if ($y0 < 75 && $y >= 75) {
            $inc[] = ['s75', $path];
        }
        if ($y0 < 100 && $y >= 100) {
            $inc[] = ['s100', $path];
        }

        $ph = 0;
        $dwell = [];
        if ($slug !== null) {
            $pv = self::keys((string) $request->input('pv'));
            foreach ($pv as $k) {
                $inc[] = ['pic', $slug . '|' . $k];
            }
            $ph = count($pv);
            if ($ph) {
                $inc[] = ['photos', $slug, $ph];
            }
            foreach (array_slice(explode(',', (string) $request->input('pt')), 0, 80) as $pair) {
                if (preg_match('~^([a-f0-9]{8}):(\d{1,4})$~', $pair, $m) && ($sec = min(self::MAX_SECONDS, (int) $m[2])) > 0) {
                    $inc[] = ['pict', $slug . '|' . $m[1], $sec];
                    $dwell[$m[1]] = ($dwell[$m[1]] ?? 0) + $sec;
                }
            }
            foreach (self::keys((string) $request->input('pz')) as $k) {
                $inc[] = ['picz', $slug . '|' . $k];
            }
        }
        if (! $inc) {
            return false;
        }

        if ($seen->visit) {
            self::step((int) $seen->visit, $t, function (array $steps) use ($path, $s, $ph) {
                for ($i = count($steps) - 1; $i >= 0; $i--) {
                    if ($steps[$i]['p'] === $path) {
                        $steps[$i]['d'] = min(self::MAX_SECONDS, ($steps[$i]['d'] ?? 0) + $s);
                        if ($ph) {
                            $steps[$i]['ph'] = ($steps[$i]['ph'] ?? 0) + $ph;
                        }
                        break;
                    }
                }

                return $steps;
            });
        }

        self::commit($seen, $day, $t, $inc);

        return true;
    }

    /**
     * WhatsApp, the phone, e-mail, and any data-stat press: once per person, day
     * and page — "pulsaron WhatsApp" means people, not presses. $k is what the
     * press was about when the page says (data-stat-k: a marque on /catalogo).
     */
    private static function press(Request $request, string $path, string $metric, string $key, string $k = ''): bool
    {
        [$seen, $day, $t] = self::seen($request);
        if (! $seen) {
            return false;
        }
        $done = self::done($seen);
        $word = $metric === 'ev' ? explode('|', $key, 2)[0] : $metric;

        $inc = [];
        if (! isset($done[$metric . "\t" . $key])) {
            $inc[] = [$metric, $key];
        }
        if ($metric === 'wa' && ! isset($done["wap\t"])) {
            $inc[] = ['wap', ''];                       // people who pressed WhatsApp at all
        }
        if ($k !== '' && ! isset($done["evk\t" . $word . '|' . $k])) {
            $inc[] = ['evk', $word . '|' . $k];
        }

        if ($seen->visit) {
            self::step((int) $seen->visit, $t, fn (array $steps) => self::mark($steps, $path, $word), false, $metric === 'wa');
        }
        self::commit($seen, $day, $t, $inc);

        return true;
    }

    private static function event(Request $request, string $path): bool
    {
        $name = (string) $request->input('e');
        if (! preg_match('~^[a-z0-9-]{1,24}$~', $name)) {
            return false;
        }

        return self::press($request, $path, 'ev', $name . '|' . $path, self::k($request));
    }

    /** A photograph, as the car page and the beacon name it: the first 8 hex of md5(its stored path). */
    public static function photoKey(string $path): string
    {
        return substr(md5($path), 0, 8);
    }

    /** data-stat-k, checked: a marque key or the like. */
    private static function k(Request $request): string
    {
        $k = strtolower((string) $request->input('k'));

        return preg_match('~^[a-z0-9-]{1,24}$~', $k) ? $k : '';
    }

    /** @return list<string> photograph keys, each once */
    private static function keys(string $csv): array
    {
        $out = [];
        foreach (array_slice(explode(',', $csv), 0, 80) as $k) {
            if (preg_match('~^[a-f0-9]{8}$~', $k)) {
                $out[$k] = $k;
            }
        }

        return array_values($out);
    }

    /** @param list<array> $steps */
    private static function mark(array $steps, string $path, string $word): array
    {
        for ($i = count($steps) - 1; $i >= 0; $i--) {
            if ($steps[$i]['p'] === $path) {
                $e = $steps[$i]['e'] ?? [];
                if (count($e) < 12) {
                    $e[] = $word;
                }
                $steps[$i]['e'] = $e;
                break;
            }
        }

        return $steps;
    }

    /** Rewrites a visit's steps through $fn; $page counts a new page, $wa marks a WhatsApp press. */
    private static function step(int $visit, int $t, callable $fn, bool $page = false, bool $wa = false): void
    {
        $v = DB::table('stat_visits')->where('id', $visit)->first(['started_at', 'steps', 'pages']);
        if (! $v) {
            return;
        }
        $steps = json_decode((string) $v->steps, true) ?: [];
        $steps = $fn($steps);
        foreach ($steps as &$s) {
            $s['t'] ??= $t - (int) $v->started_at;
        }
        unset($s);

        $set = ['steps' => json_encode($steps, self::JSON), 'last_at' => $t];
        if ($page) {
            $set['pages'] = (int) $v->pages + 1;
        }
        if ($wa) {
            $set['wa'] = true;
        }
        DB::table('stat_visits')->where('id', $visit)->update($set);
    }

    /**
     * Today's row for this person — or yesterday's, when they were there in the
     * last half hour: a page opened at 23:58 and left at 00:03 still reports
     * its minutes, and a WhatsApp press just after midnight still counts. What
     * it adds goes to the day of that row, next to the page view it belongs to.
     *
     * @return array{0:?object,1:string,2:int} the row, its day, the time
     */
    private static function seen(Request $request): array
    {
        $now = self::clock();
        $t = $now->getTimestamp();
        $ip = (string) $request->ip();
        $ua = (string) $request->userAgent();
        $day = $now->toDateString();
        $row = DB::table('stat_seen')->where('day', $day)->where('hash', self::hash($day, $ip, $ua))->first();
        if (! $row) {
            $y = $now->subDay()->toDateString();
            $old = DB::table('stat_seen')->where('day', $y)->where('hash', self::hash($y, $ip, $ua))->first();
            if ($old && $t - (int) $old->last_at <= self::VISIT_GAP) {
                return [$old, $y, $t];
            }
        }

        return [$row, $day, $t];
    }

    /** Counts $inc on the row's day and remembers it on the row, so it can be taken out again. */
    private static function commit(object $seen, string $day, int $t, array $inc): void
    {
        $set = ['last_at' => max($t, (int) $seen->last_at)];
        if ($inc) {
            $set['inc'] = json_encode(self::add(self::done($seen), $inc), self::JSON);
        }
        DB::table('stat_seen')->where('day', $day)->where('hash', $seen->hash)->update($set);
        self::bump($day, $inc);
    }

    /** This person-day's town key, as view() counted it. */
    private static function doneGeo(array $done): string
    {
        foreach ($done as $id => $n) {
            if (str_starts_with((string) $id, "geo\t")) {
                return substr((string) $id, 4);
            }
        }

        return '';
    }

    /** @return array<string,mixed> what this person-day has added so far ("metric\tkey" → n, "_v" → visit ids) */
    private static function done(object $seen): array
    {
        return json_decode((string) ($seen->inc ?? ''), true) ?: [];
    }

    private static function add(array $done, array $inc): array
    {
        foreach ($inc as $i) {
            $id = $i[0] . "\t" . mb_substr((string) $i[1], 0, 160);
            $done[$id] = ($done[$id] ?? 0) + (int) ($i[2] ?? 1);
        }

        return $done;
    }

    private static function hash(string $day, string $ip, string $ua): string
    {
        return substr(hash('sha256', self::salt($day) . (self::$team ? '|team' : '') . '|' . $ip . '|' . $ua), 0, 32);
    }

    /** The connection alone, for the day: all the browsers behind one address. */
    private static function iph(string $day, string $ip): string
    {
        return substr(hash('sha256', self::salt($day) . '|ip|' . $ip), 0, 32);
    }

    /**
     * A random salt per day, in the database: the first request of the day
     * writes it (insertOrIgnore, so two first requests agree), every later one
     * reads it. Deleted with the day's people after two days.
     */
    private static function salt(string $day): string
    {
        DB::table('stat_salt')->insertOrIgnore(['day' => $day, 'salt' => bin2hex(random_bytes(16))]);

        return (string) DB::table('stat_salt')->where('day', $day)->value('salt');
    }

    /**
     * Runs $fn with the statistics to itself. SQLite takes one writer at a time
     * anyway; this makes "read the person, then write them" one step, so two
     * beacons of the same person never both find them new.
     */
    private static function locked(callable $fn): bool
    {
        $h = @fopen(storage_path('framework/cache/stats.lock'), 'c');
        if (! $h) {
            return (bool) $fn();
        }
        try {
            $give = microtime(true) + 3;
            while (! flock($h, LOCK_EX | LOCK_NB)) {
                if (microtime(true) > $give) {
                    return false;
                }
                usleep(5000);
            }

            return (bool) $fn();
        } finally {
            flock($h, LOCK_UN);
            fclose($h);
        }
    }

    /** @param list<array{0:string,1:string,2?:int}> $inc */
    private static function bump(string $day, array $inc): void
    {
        if (self::$team) {
            return;                                         // the team's visits are shown, never counted
        }
        $rows = [];
        foreach ($inc as $i) {
            $key = mb_substr((string) $i[1], 0, 160);
            $id = $i[0] . "\0" . $key;
            $n = (int) ($i[2] ?? 1);
            if (isset($rows[$id])) {
                $rows[$id]['n'] += $n;
            } else {
                $rows[$id] = ['day' => $day, 'metric' => $i[0], 'key' => $key, 'n' => $n];
            }
        }
        if ($rows) {
            DB::table('stats')->upsert(array_values($rows), ['day', 'metric', 'key'], ['n' => DB::raw('"stats"."n" + "excluded"."n"')]);
        }
    }

    private static function bucket(int $y): int
    {
        return min(100, max(0, intdiv($y, 25) * 25));
    }

    /** @return array<string,string> */
    private static function query(string $q): array
    {
        parse_str(ltrim($q, '?'), $out);

        return array_filter($out, 'is_string');
    }

    /** What the home search asked the catalogue for. @return list<string> */
    public static function searches(array $q): array
    {
        $out = [];
        $marca = strtolower(trim((string) ($q['marca'] ?? '')));
        if ($marca !== '' && preg_match('~^[a-z0-9-]{2,24}$~', $marca)) {
            $out[] = 'marca:' . $marca;
        }
        $modelo = trim(preg_replace('~\s+~u', ' ', strip_tags((string) ($q['modelo'] ?? ''))));
        if ($modelo !== '' && preg_match('~^[\pL\pN .\-/+]{1,60}$~u', $modelo)) {
            $out[] = 'modelo:' . mb_strtolower($modelo);
        }
        $max = (int) ($q['max'] ?? 0);
        if ($max >= 50 && $max <= 2000000) {
            $out[] = (($q['pago'] ?? '') === 'mes' ? 'cuota:' : 'max:') . $max;
        }

        return $out;
    }

    /**
     * Where a visit came from: the page's own document.referrer, and the in-app
     * browsers that say who they are. iPhone WhatsApp passes nothing, so it lands
     * in "directo" — the panel says so.
     */
    public static function source(string $ref, string $ua, string $own = ''): string
    {
        if (str_contains($ua, 'Instagram')) {
            return 'instagram';
        }
        if (preg_match('~FBAN|FBAV|FB_IAB~', $ua)) {
            return 'facebook';
        }

        $ref = strtolower(trim($ref));
        if ($ref === '') {
            return 'directo';
        }

        if (str_starts_with($ref, 'android-app://')) {
            $app = (string) parse_url($ref, PHP_URL_HOST);

            return match (true) {
                str_contains($app, 'whatsapp')                 => 'whatsapp',
                str_contains($app, 'instagram')                => 'instagram',
                str_contains($app, 'facebook')                 => 'facebook',
                str_contains($app, 'google')                   => 'google',
                str_contains($app, 'tiktok') || str_contains($app, 'musically') => 'tiktok',
                default                                        => 'otras',
            };
        }

        $host = preg_replace('~^www\.~', '', (string) parse_url($ref, PHP_URL_HOST));
        $own = preg_replace('~^www\.~', '', strtolower($own));
        if ($host === '' || ($own !== '' && $host === $own)) {
            return 'directo';
        }

        $is = fn (string $re) => (bool) preg_match('~(^|\.)(' . $re . ')$~', $host);

        return match (true) {
            (bool) preg_match('~(^|\.)google\.[a-z.]{2,8}$~', $host) => 'google',
            $is('bing\.com|duckduckgo\.com|yahoo\.com|ecosia\.org|search\.brave\.com|qwant\.com|yandex\.[a-z]+|startpage\.com') => 'buscadores',
            $is('chatgpt\.com|openai\.com|perplexity\.ai|claude\.ai|gemini\.google\.com|copilot\.microsoft\.com') => 'ia',
            $is('instagram\.com')                     => 'instagram',
            $is('facebook\.com|fb\.com|fb\.me|messenger\.com') => 'facebook',
            $is('wa\.me|whatsapp\.com|whatsapp\.net') => 'whatsapp',
            $is('tiktok\.com')                        => 'tiktok',
            $is('youtube\.com|youtu\.be')             => 'youtube',
            $is('coches\.net')                        => 'cochesnet',
            $is('wallapop\.com')                      => 'wallapop',
            $is('milanuncios\.com')                   => 'milanuncios',
            default                                   => 'otras',
        };
    }

    private static function sometimesPrune(): void
    {
        if (random_int(1, 50) === 1) {
            self::prune();
        }
    }

    /** Yesterday's people and older go, with their salts; the team's connections when they lapse; visits after 30 days. */
    public static function prune(): int
    {
        $now = self::clock();
        $yesterday = $now->subDay()->toDateString();
        $n = DB::table('stat_seen')->where('day', '<', $yesterday)->delete();
        DB::table('stat_salt')->where('day', '<', $yesterday)->delete();
        DB::table('stat_staff')->where('until', '<', self::clock()->getTimestamp())->delete();
        $n += DB::table('stat_visits')->where('day', '<', $now->subDays(self::KEEP_VISITS)->toDateString())->delete();

        return $n;
    }

    /* =====================================================================
       Reading
       ===================================================================== */

    public static function firstDay(): ?CarbonImmutable
    {
        // the browser's own counting; the log estimate (h.*) is not "counting"
        $d = DB::table('stats')->where('metric', 'views')->min('day');

        return $d ? CarbonImmutable::parse($d, self::TZ) : null;
    }

    /** @return array{from:CarbonImmutable,to:CarbonImmutable,pFrom:CarbonImmutable,pTo:CarbonImmutable,days:int} */
    public static function period(int $days): array
    {
        $days = in_array($days, self::PERIODS, true) ? $days : 30;
        $to = self::clock()->startOfDay();
        $from = $to->subDays($days - 1);

        return ['days' => $days, 'from' => $from, 'to' => $to, 'pFrom' => $from->subDays($days), 'pTo' => $from->subDay()];
    }

    /** @return array<string,array<string,int>> metric → key → n over the days */
    private static function sums(string $from, string $to, ?array $metrics = null): array
    {
        $q = DB::table('stats')->whereBetween('day', [$from, $to]);
        if ($metrics) {
            $q->whereIn('metric', $metrics);
        }
        $by = [];
        foreach ($q->groupBy('metric', 'key')->select('metric', 'key', DB::raw('sum(n) as n'))->get() as $r) {
            $by[$r->metric][$r->key] = (int) $r->n;
        }

        return $by;
    }

    /**
     * "N vistas · M WhatsApp" for each car in a list, in one query. Null until
     * anything has been counted, so the lists say nothing rather than "0 vistas"
     * on the first day. A car missing from the map was not looked at.
     *
     * @return array<string,array{views:int,wa:int}>|null
     */
    public static function perCar(int $days = 30): ?array
    {
        if (! DB::table('stats')->where('metric', 'views')->exists()) {
            return null;
        }
        $from = self::clock()->subDays($days - 1)->toDateString();
        $out = [];
        $rows = DB::table('stats')->where('day', '>=', $from)->whereIn('metric', ['car', 'wa'])
            ->groupBy('metric', 'key')->select('metric', 'key', DB::raw('sum(n) as n'))->get();
        foreach ($rows as $r) {
            if ($r->metric === 'car') {
                $out[$r->key]['views'] = (int) $r->n;
            } elseif (str_starts_with($r->key, '/coche/')) {
                $out[substr($r->key, 7)]['wa'] = (int) $r->n;
            }
        }

        return array_map(fn ($v) => $v + ['views' => 0, 'wa' => 0], $out);
    }

    /** The teaser under the Panel's numbers: this week. */
    public static function week(): array
    {
        $from = self::clock()->subDays(6)->toDateString();
        $by = self::sums($from, self::today(), ['people', 'wap']);

        return [
            'any'    => DB::table('stats')->where('metric', 'views')->exists(),
            'people' => array_sum($by['people'] ?? []),
            'wa'     => array_sum($by['wap'] ?? []),          // people who pressed WhatsApp, not presses
        ];
    }

    /** Everything the Estadísticas page shows. */
    public static function summary(int $days, int $feed = 40): array
    {
        $p = self::period($days);
        $from = $p['from']->toDateString();
        $to = $p['to']->toDateString();
        $first = self::firstDay();

        $by = self::sums($from, $to);
        $prev = self::sums($p['pFrom']->toDateString(), $p['pTo']->toDateString(), ['people', 'visits', 'views', 'wa', 'tel', 'mail', 'time']);
        $tot = fn (array $b, string $m) => array_sum($b[$m] ?? []);

        $series = [];
        $daily = DB::table('stats')->where('metric', 'people')->where('key', '')
            ->whereBetween('day', [$from, $to])->pluck('n', 'day');
        for ($d = $p['from']; $d <= $p['to']; $d = $d->addDay()) {
            $series[] = ['day' => $d, 'n' => (int) ($daily[$d->toDateString()] ?? 0)];
        }
        $best = collect($series)->sortByDesc('n')->first();

        $people = $tot($by, 'people');
        $visits = $tot($by, 'visits');
        $views = $tot($by, 'views');

        // The previous period is compared only when it was counted in full.
        $compare = $first !== null && $first->lte($p['pFrom']);

        $sources = self::ranked($by['source'] ?? [], self::SOURCES);
        $devices = self::ranked($by['device'] ?? [], self::DEVICES);

        return [
            'days'    => $p['days'],
            'from'    => $p['from'],
            'to'      => $p['to'],
            'first'   => $first,
            'any'     => $first !== null,
            'partial' => $first !== null && $first->gt($p['from']),
            'compare' => $compare,
            'people'  => [$people, $tot($prev, 'people')],
            'visits'  => [$visits, $tot($prev, 'visits')],
            'views'   => [$views, $tot($prev, 'views')],
            'wa'      => [$tot($by, 'wa'), $tot($prev, 'wa')],
            'wap'     => $tot($by, 'wap'),
            'tel'     => [$tot($by, 'tel'), $tot($prev, 'tel')],
            'mail'    => [$tot($by, 'mail'), $tot($prev, 'mail')],
            'visitTime' => $visits ? (int) round($tot($by, 'time') / $visits) : 0,
            'today'   => (int) ($daily[self::today()] ?? 0),
            'now'     => self::live(),
            'series'  => $series,
            'best'    => ($best && $best['n'] > 0 && count(array_filter($series, fn ($s) => $s['n'] > 0)) > 1) ? $best : null,
            'mobile'  => ($dev = array_sum($by['device'] ?? [])) ? (int) round(100 * ($by['device']['mobile'] ?? 0) / $dev) : null,
            'sources' => $sources,
            'devices' => $devices,
            'pages'   => self::pages($by),
            'cars'    => self::cars($by),
            'search'  => self::searchList($by['search'] ?? []),
            'events'  => self::events($by['ev'] ?? []),
            'visitsFeed' => self::feed($p['from'], $feed),
            'db'      => self::database($p['from'], $p['to'], $p['pFrom'], $p['pTo']),
            'brands'  => StatsInsights::brands($by),
            'prices'  => StatsInsights::prices($by),
            'places'  => StatsInsights::places($by['geo'] ?? []),
            'photos'  => StatsInsights::photos($by),
            'history' => StatsInsights::history(),
            'me'      => \Illuminate\Support\Facades\URL::signedRoute('stats.me'),
            'team'    => self::feed(self::clock()->subDay()->startOfDay(), 12, null, true),
        ];
    }

    /** @return list<array{key:string,label:string,n:int,pct:int}> */
    private static function ranked(array $counts, array $labels): array
    {
        arsort($counts);
        $sum = max(1, array_sum($counts));
        $out = [];
        foreach ($counts as $k => $n) {
            if ($n <= 0) {
                continue;
            }
            $out[] = ['key' => (string) $k, 'label' => $labels[$k] ?? (string) $k, 'n' => $n, 'pct' => (int) round(100 * $n / $sum)];
        }

        return $out;
    }

    /** People on the site in the last five minutes, and where. */
    public static function live(): array
    {
        $since = self::clock()->getTimestamp() - self::NOW;
        $rows = DB::table('stat_seen')->where('team', false)->where('last_at', '>=', $since)->pluck('last_path');
        $names = self::carNames($rows->filter(fn ($p) => str_starts_with($p, '/coche/'))->map(fn ($p) => substr($p, 7))->all());
        $where = [];
        foreach ($rows->countBy() as $path => $n) {
            $where[] = ['label' => self::label($path, $names), 'n' => $n];
        }
        usort($where, fn ($a, $b) => $b['n'] <=> $a['n']);

        return ['n' => $rows->count(), 'where' => $where];
    }

    private static function pages(array $by): array
    {
        $out = [];
        foreach (self::PAGES as $path => $label) {
            $n = (int) ($by['page'][$path] ?? 0);
            if ($n === 0) {
                continue;
            }
            $out[] = ['label' => $label, 'n' => $n] + self::depth($by, $path);
        }
        $cars = array_sum($by['car'] ?? []);
        if ($cars) {
            $timen = $time = $s100 = 0;
            foreach ($by['timen'] ?? [] as $k => $v) {
                if (str_starts_with($k, '/coche/')) {
                    $timen += $v;
                    $time += $by['time'][$k] ?? 0;
                    $s100 += $by['s100'][$k] ?? 0;
                }
            }
            $out[] = ['label' => 'Fichas de coches', 'n' => $cars,
                'time' => $timen ? (int) round($time / $timen) : null,
                'end' => $timen ? (int) round(100 * $s100 / $timen) : null];
        }
        usort($out, fn ($a, $b) => $b['n'] <=> $a['n']);
        $max = max(1, $out[0]['n'] ?? 1);

        return array_map(fn ($r) => $r + ['pct' => (int) round(100 * $r['n'] / $max)], $out);
    }

    /** @return array{time:?int,end:?int} average seconds, % that reached the end */
    private static function depth(array $by, string $path): array
    {
        $n = (int) ($by['timen'][$path] ?? 0);

        return [
            'time' => $n ? (int) round(($by['time'][$path] ?? 0) / $n) : null,
            'end'  => $n ? (int) round(100 * ($by['s100'][$path] ?? 0) / $n) : null,
        ];
    }

    /**
     * Every car for sale (a car nobody looks at is the news) plus any sold one
     * that was looked at.
     */
    private static function cars(array $by): array
    {
        $seen = array_keys($by['car'] ?? []);
        $cars = Vehicle::query()
            ->where(fn ($q) => $q->where('status', 'available')->orWhereIn('slug', $seen ?: ['-']))
            ->get(['id', 'slug', 'brand', 'model', 'year', 'price', 'status', 'cover_image', 'gallery_images']);

        $out = [];
        foreach ($cars as $c) {
            $path = '/coche/' . $c->slug;
            $views = (int) ($by['car'][$c->slug] ?? 0);
            $timen = (int) ($by['timen'][$path] ?? 0);
            $people = (int) ($by['carp'][$c->slug] ?? 0);
            $wa = (int) ($by['wa'][$path] ?? 0);
            $out[] = [
                'slug'   => $c->slug,
                'name'   => trim($c->brand . ' ' . $c->model),
                'year'   => $c->year,
                'img'    => $c->primary_image,
                'sold'   => $c->status === 'sold',
                'price'  => $c->price,
                'views'  => $views,
                'people' => $people,
                'wa'     => $wa,
                'tel'    => (int) ($by['tel'][$path] ?? 0),
                'photos' => $timen ? round(($by['photos'][$c->slug] ?? 0) / $timen, 1) : null,
                'time'   => $timen ? (int) round(($by['time'][$path] ?? 0) / $timen) : null,
                'end'    => $timen ? (int) round(100 * ($by['s100'][$path] ?? 0) / $timen) : null,
                'qr'     => (int) ($by['qr'][$c->slug] ?? 0),
                'rate'   => $people >= 20 ? (int) round(100 * ($wa + (int) ($by['tel'][$path] ?? 0)) / $people) : null,
            ];
        }
        usort($out, fn ($a, $b) => [$b['views'], $b['wa'], (int) $a['sold']] <=> [$a['views'], $a['wa'], (int) $b['sold']]);
        $max = max(1, $out[0]['views'] ?? 1);

        return array_map(fn ($r) => $r + ['pct' => (int) round(100 * $r['views'] / $max)], $out);
    }

    private static function searchList(array $search): array
    {
        $out = ['marca' => [], 'modelo' => [], 'precio' => []];
        $names = array_column(Marques::ALL, 'name', 'key');
        foreach ($search as $k => $n) {
            [$kind, $v] = explode(':', $k, 2) + [1 => ''];
            match ($kind) {
                'marca'  => $out['marca'][] = ['label' => $names[$v] ?? ucfirst($v), 'n' => $n],
                'modelo' => $out['modelo'][] = ['label' => $v, 'n' => $n],
                'max'    => $out['precio'][] = ['label' => 'hasta ' . self::eur((int) $v), 'n' => $n, 'v' => (int) $v],
                'cuota'  => $out['precio'][] = ['label' => 'hasta ' . self::eur((int) $v) . ' al mes', 'n' => $n, 'v' => (int) $v + 10000000],
                default  => null,
            };
        }
        foreach (['marca', 'modelo'] as $k) {
            usort($out[$k], fn ($a, $b) => $b['n'] <=> $a['n']);
        }
        usort($out['precio'], fn ($a, $b) => $a['v'] <=> $b['v']);

        return array_filter($out);
    }

    private static function events(array $ev): array
    {
        $by = [];
        foreach ($ev as $k => $n) {
            $name = explode('|', $k, 2)[0];
            $by[$name] = ($by[$name] ?? 0) + $n;
        }
        arsort($by);
        $out = [];
        foreach ($by as $name => $n) {
            $out[] = ['label' => self::EVENTS[$name][0] ?? $name, 'n' => $n];
        }

        return $out;
    }

    /** The latest visits, newest first, ready to read. */
    public static function feed(CarbonImmutable $from, int $limit, ?string $car = null, bool $team = false): array
    {
        $q = DB::table('stat_visits')->where('team', $team)->where('day', '>=', $from->toDateString());
        if ($car !== null) {
            $q->whereRaw('instr("steps", ?) > 0', ['"/coche/' . $car . '"']);
        }
        $rows = $q->orderByDesc('last_at')->limit($limit)->get();

        $slugs = [];
        foreach ($rows as $r) {
            foreach (json_decode((string) $r->steps, true) ?: [] as $s) {
                if (str_starts_with($s['p'] ?? '', '/coche/')) {
                    $slugs[] = substr($s['p'], 7);
                }
            }
        }
        $names = self::carNames($slugs);
        $short = array_map(fn ($e) => $e[1], self::EVENTS) + ['wa' => 'WhatsApp', 'tel' => 'pulsó llamar', 'mail' => 'pulsó e-mail'];

        return $rows->map(function ($r) use ($names, $short) {
            $steps = [];
            foreach (json_decode((string) $r->steps, true) ?: [] as $s) {
                $steps[] = [
                    'label'  => self::label($s['p'] ?? '', $names),
                    'href'   => $s['p'] ?? null,
                    'd'      => isset($s['d']) ? (int) $s['d'] : null,
                    'ph'     => (int) ($s['ph'] ?? 0),
                    'events' => array_map(fn ($e) => $short[$e] ?? $e, $s['e'] ?? []),
                    'wa'     => in_array('wa', $s['e'] ?? [], true),
                ];
            }

            return [
                'at'     => CarbonImmutable::createFromTimestamp((int) $r->started_at, self::TZ),
                'last'   => CarbonImmutable::createFromTimestamp((int) $r->last_at, self::TZ),
                'who'    => self::who($r->device, $r->os, $r->browser),
                'source' => self::SOURCES[$r->source] ?? $r->source,
                'place'  => (string) ($r->place ?? ''),
                'steps'  => $steps,
                'wa'     => (bool) $r->wa,
            ];
        })->all();
    }

    /** "iPhone", "Android", "Ordenador · Windows". */
    public static function who(string $device, string $os, string $browser): string
    {
        return match (true) {
            $os === 'iOS'                         => $device === 'tablet' ? 'iPad' : 'iPhone',
            $os === 'Android'                     => $device === 'tablet' ? 'Tableta Android' : 'Android',
            $device === 'desktop' && $os === 'macOS' => 'Mac',
            $device === 'desktop'                 => 'Ordenador' . ($os && $os !== 'otro' ? ' · ' . $os : ''),
            default                               => 'Móvil',
        };
    }

    /** @param list<string> $slugs @return array<string,string> slug → "BMW 318d 2020" */
    private static function carNames(array $slugs): array
    {
        $slugs = array_values(array_unique($slugs));
        if (! $slugs) {
            return [];
        }

        return Vehicle::whereIn('slug', $slugs)->get(['slug', 'brand', 'model', 'year'])
            ->mapWithKeys(fn ($c) => [$c->slug => trim($c->brand . ' ' . $c->model . ' ' . $c->year)])->all();
    }

    private static function label(string $path, array $names): string
    {
        if (str_starts_with($path, '/coche/')) {
            return $names[substr($path, 7)] ?? 'Un coche';
        }

        return self::PAGES[$path] ?? $path;
    }

    /** The numbers that are not traffic: the panel's own records, over the same days. */
    private static function database(CarbonImmutable $from, CarbonImmutable $to, CarbonImmutable $pFrom, CarbonImmutable $pTo): array
    {
        // created_at is stored in UTC; a Madrid day is turned into its UTC span.
        $in = fn (CarbonImmutable $a, CarbonImmutable $b) => [
            $a->startOfDay()->utc()->toDateTimeString(), $b->endOfDay()->utc()->toDateTimeString(),
        ];

        // Spam is told apart over the whole inbox (it is recognised by repeats).
        $real = Inbox::sort(ContactMessage::query()->get(['id', 'name', 'subject', 'message', 'created_at']))['real'];
        $count = function ($a, $b) use ($real, $in) {
            [$x, $y] = $in($a, $b);

            return $real->filter(fn ($m) => $m->created_at && ($t = $m->created_at->copy()->utc()->toDateTimeString()) >= $x && $t <= $y)->count();
        };

        $offers = fn ($a, $b) => Vehicle::where('offer_type', SellCarController::OFFER_TYPE)->whereBetween('created_at', $in($a, $b))->count();

        $live = Vehicle::where('status', 'available')->get(['slug', 'brand', 'model', 'year', 'price', 'created_at']);
        $now = CarbonImmutable::now();
        $age = $live->map(fn ($c) => $c->created_at ? (int) $c->created_at->diffInDays($now) : null)->filter(fn ($d) => $d !== null);
        $oldest = $live->filter(fn ($c) => $c->created_at)->sortBy('created_at')->first();

        return [
            'messages'  => [$count($from, $to), $count($pFrom, $pTo)],
            'offers'    => [$offers($from, $to), $offers($pFrom, $pTo)],
            'referrers' => Referrer::whereBetween('created_at', $in($from, $to))->count(),
            'live'      => $live->count(),
            'value'     => (int) $live->sum('price'),
            'avg'       => $live->count() ? (int) round($live->avg('price')) : 0,
            'age'       => $age->count() ? (int) round($age->avg()) : null,
            'oldest'    => $oldest ? ['name' => trim($oldest->brand . ' ' . $oldest->model . ' ' . $oldest->year),
                'slug' => $oldest->slug, 'days' => (int) $oldest->created_at->diffInDays($now)] : null,
            'added'     => Vehicle::whereBetween('created_at', $in($from, $to))
                ->where(fn ($q) => $q->whereNull('offer_type')->orWhere('offer_type', '!=', SellCarController::OFFER_TYPE))->count(),
            'sold'      => Vehicle::where('status', 'sold')->count(),
            'soldNow'   => Vehicle::where('status', 'sold')->whereDate('sold_date', '>=', $from->toDateString())
                ->whereDate('sold_date', '<=', $to->toDateString())->count(),
            'reviews'   => Testimonial::where('is_active', true)->count(),
        ];
    }

    /**
     * One car: 7 days, 30 days and since counting began, its daily people, and
     * where they came from.
     */
    public static function car(Vehicle $car, int $days = 30): array
    {
        $slug = $car->slug;
        $path = '/coche/' . $slug;
        $first = self::firstDay();
        $today = self::clock()->startOfDay();

        $rows = DB::table('stats')
            ->where(fn ($q) => $q->whereIn('key', [$slug, $path])
                ->orWhere(fn ($q) => $q->where('key', '>', $slug . '|')->where('key', '<', $slug . '}'))
                ->orWhere(fn ($q) => $q->where('metric', 'ev')->where('key', 'like', '%|' . $path)))
            ->get(['day', 'metric', 'key', 'n']);

        $win = function (?string $from) use ($rows, $slug, $path) {
            $m = [];
            foreach ($rows as $r) {
                if ($from !== null && $r->day < $from) {
                    continue;
                }
                $k = match (true) {
                    $r->metric === 'ev'     => 'ev:' . explode('|', $r->key)[0],
                    $r->metric === 'carsrc' => 'src:' . explode('|', $r->key, 2)[1],
                    $r->metric === 'cargeo' => 'geo:' . explode('|', $r->key, 2)[1],
                    default                 => $r->metric,
                };
                if (! in_array($r->metric, ['ev', 'carsrc', 'cargeo'], true) && $r->key !== $slug && $r->key !== $path) {
                    continue;
                }
                $m[$k] = ($m[$k] ?? 0) + (int) $r->n;
            }
            $timen = $m['timen'] ?? 0;
            $people = $m['carp'] ?? 0;
            $act = ($m['wa'] ?? 0) + ($m['tel'] ?? 0);

            return [
                'views'  => $m['car'] ?? 0,
                'people' => $people,
                'wa'     => $m['wa'] ?? 0,
                'tel'    => $m['tel'] ?? 0,
                'photos' => $timen ? round(($m['photos'] ?? 0) / $timen, 1) : null,
                'video'  => $m['ev:video'] ?? 0,
                'time'   => $timen ? (int) round(($m['time'] ?? 0) / $timen) : null,
                'end'    => $timen ? (int) round(100 * ($m['s100'] ?? 0) / $timen) : null,
                'qr'     => $m['qr'] ?? 0,
                'share'  => $m['ev:compartir'] ?? 0,
                'rate'   => $people >= 20 && $act ? (int) round(100 * $act / $people) : null,
                'm'      => $m,
            ];
        };

        $p = self::period($days);
        $cur = $win($p['from']->toDateString());
        $series = [];
        $daily = [];
        foreach ($rows as $r) {
            if ($r->metric === 'carp' && $r->key === $slug) {
                $daily[$r->day] = (int) $r->n;
            }
        }
        for ($d = $p['from']; $d <= $p['to']; $d = $d->addDay()) {
            $series[] = ['day' => $d, 'n' => $daily[$d->toDateString()] ?? 0];
        }
        $src = $ev = $geo = [];
        foreach ($cur['m'] as $k => $n) {
            if (str_starts_with($k, 'geo:')) {
                $geo[substr($k, 4)] = $n;
            } elseif (str_starts_with($k, 'src:')) {
                $src[substr($k, 4)] = $n;
            } elseif (str_starts_with($k, 'ev:')) {
                $ev[substr($k, 3)] = $n;
            }
        }

        return [
            'any'     => $first !== null,
            'first'   => $first,
            'days'    => $p['days'],
            'from'    => $p['from'],
            'w7'      => $win($today->subDays(6)->toDateString()),
            'w30'     => $win($today->subDays(29)->toDateString()),
            'all'     => $win(null),
            'cur'     => $cur,
            'series'  => $series,
            'sources' => self::ranked($src, self::SOURCES),
            'events'  => self::events($ev),
            'places'  => self::towns($geo),
            'photos'  => StatsInsights::carPhotos($car, $p['from']->toDateString(), $p['to']->toDateString()),
            'before'  => self::before($car),
            'feed'    => self::feed($p['from'], 30, $slug),
        ];
    }

    /** "ES|Málaga" → n, as a ranked list of towns ("Málaga", "Chisinau (Moldavia)"). */
    private static function towns(array $geo): array
    {
        $out = [];
        foreach ($geo as $k => $n) {
            [$cc, $city] = explode('|', (string) $k, 2) + [1 => ''];
            $label = $city !== '' ? $city : Geo::country($cc);
            if ($cc !== 'ES' && $city !== '') {
                $label .= ' (' . Geo::country($cc) . ')';
            }
            $out[$label] = ($out[$label] ?? 0) + $n;
        }

        return self::ranked($out, []);
    }

    /**
     * What the old site's counter said about this car, until the new site went
     * live on 6.10: every opening of its page, robots and the owner included.
     * Only good for comparing cars listed over the same weeks.
     *
     * @return array{n:int,days:int}|null
     */
    private static function before(Vehicle $car): ?array
    {
        $n = (int) $car->views_count;
        if ($n <= 0 || ! $car->created_at) {
            return null;
        }
        $end = CarbonImmutable::parse('2026-10-06', self::TZ);
        $days = max(1, (int) $car->created_at->diffInDays($end));

        return $car->created_at->lt($end) ? ['n' => $n, 'days' => $days] : null;
    }

    /* ---- formatting, shared by the views ---------------------------------- */

    public static function num(int|float|null $n): string
    {
        return $n === null ? '—' : number_format((float) $n, is_float($n) && floor($n) != $n ? 1 : 0, ',', '.');
    }

    public static function eur(int|float $n): string
    {
        return number_format((float) $n, 0, ',', '.') . ' €';
    }

    /** 75 → "1 min 15 s", 40 → "40 s". */
    public static function dur(?int $s): string
    {
        if ($s === null) {
            return '—';
        }
        if ($s < 60) {
            return $s . ' s';
        }
        $m = intdiv($s, 60);
        $r = $s % 60;

        return $m . ' min' . ($r && $m < 10 ? ' ' . $r . ' s' : '');
    }

    /** "+18 %" / "−5 %" / null when there is nothing to compare with. */
    public static function delta(int $now, int $before): ?string
    {
        if ($before <= 0) {
            return null;
        }
        $pct = (int) round(100 * ($now - $before) / $before);

        return ($pct > 0 ? '+' : ($pct < 0 ? '−' : '')) . abs($pct) . ' %';
    }
}
