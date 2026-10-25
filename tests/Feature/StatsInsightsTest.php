<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Support\Stats;
use App\Support\StatsHistory;
use App\Support\StatsInsights;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** What the counters are turned into (App\Support\StatsInsights, StatsHistory), and the hooks the pages carry for them. */
class StatsInsightsTest extends TestCase
{
    use RefreshDatabase;

    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0.1 Mobile/15E148 Safari/604.1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-24 12:00:00', Stats::TZ));
        foreach ([
            ['bmw-318d-2020', 'BMW', '318d', 21900, 'available'],
            ['bmw-x1-2017', 'Bmw', 'X1', 17900, 'available'],
            ['vw-golf-2016', 'Volkswagen', 'Golf', 13800, 'available'],
            ['seat-ibiza-2010', 'Seat', 'Ibiza', 5000, 'sold'],
        ] as [$slug, $brand, $model, $price, $status]) {
            Vehicle::create(['slug' => $slug, 'brand' => $brand, 'model' => $model, 'title' => 'x', 'year' => 2020,
                'price' => $price, 'mileage' => 90000, 'status' => $status,
                'cover_image' => '/storage/vehicles/' . $slug . '/a.jpg',
                'gallery_images' => ['/storage/vehicles/' . $slug . '/a.jpg', '/storage/vehicles/' . $slug . '/b.jpg', '/storage/vehicles/' . $slug . '/c.jpg']]);
        }
    }

    private function row(string $metric, string $key, int $n, string $day = '2026-10-24'): void
    {
        DB::table('stats')->insert(['day' => $day, 'metric' => $metric, 'key' => $key, 'n' => $n]);
    }

    private function by(): array
    {
        $by = [];
        foreach (DB::table('stats')->get() as $r) {
            $by[$r->metric][$r->key] = ($by[$r->metric][$r->key] ?? 0) + $r->n;
        }

        return $by;
    }

    public function test_marques_people_per_car_for_sale_and_what_is_asked_for(): void
    {
        $this->row('carp', 'bmw-318d-2020', 30);
        $this->row('carp', 'bmw-x1-2017', 10);
        $this->row('carp', 'vw-golf-2016', 10);
        $this->row('wa', '/coche/bmw-318d-2020', 4);
        $this->row('search', 'marca:porsche', 2);
        $this->row('evk', 'marca-abrir|porsche', 1);
        $this->row('evk', 'avisame|porsche', 1);

        $b = StatsInsights::brands($this->by());
        $rows = collect($b['rows'])->keyBy('key');
        $this->assertSame(40, $rows['bmw']['people']);          // "BMW" and "Bmw" are one marque
        $this->assertSame(2, $rows['bmw']['stock']);
        $this->assertSame(20.0, $rows['bmw']['perCar']);
        $this->assertSame(10.0, $rows['volkswagen']['perCar']);
        $this->assertSame(80, $rows['bmw']['share']);
        $this->assertSame(4, $rows['bmw']['wa']);
        $this->assertSame(4, $rows['porsche']['asked']);
        $this->assertFalse($rows->has('seat'));                  // sold long ago, nobody looks: not a decision
        $this->assertTrue($b['enough']);
        $say = implode(' ', $b['say']);
        $this->assertStringContainsString('BMW es la que más atrae: 20 personas por coche', $say);
        $this->assertStringContainsString('Preguntan por Porsche (4 veces', $say);
    }

    public function test_no_winner_is_named_from_a_handful_of_people(): void
    {
        $this->row('carp', 'bmw-318d-2020', 3);
        $b = StatsInsights::brands($this->by());
        $this->assertFalse($b['enough']);
        $this->assertCount(1, $b['say']);
        $this->assertStringContainsString('Todavía son pocos datos: 3 personas', $b['say'][0]);
        $this->assertSame([], StatsInsights::prices($this->by())['say']);
    }

    public function test_price_bands(): void
    {
        $this->row('carp', 'bmw-318d-2020', 20);
        $this->row('carp', 'vw-golf-2016', 20);
        $this->row('search', 'max:20000', 3);
        $p = StatsInsights::prices($this->by());
        $bands = collect($p['rows'])->keyBy('label');
        $this->assertSame(20, $bands['20.000–25.000 €']['people']);
        $this->assertSame(1, $bands['15.000–20.000 €']['stock']);    // the X1, nobody looked
        $this->assertSame([['label' => '15.000–20.000 €', 'n' => 3]], $p['asked']);   // "up to 20.000" is the band below
    }

    public function test_places_near_malaga_the_rest_of_andalucia_spain_abroad_and_hidden(): void
    {
        $g = StatsInsights::places([
            'ES|Andalusia|Málaga|36.72|-4.42'          => 5,
            'ES|Andalusia|Marbella|36.51|-4.88'        => 2,
            'ES|Andalusia|Seville|37.38|-5.97'         => 3,
            'ES|Madrid|Madrid|40.42|-3.7'              => 4,
            'MD|Chișinău Municipality|Chisinau|47.0|28.86' => 1,
            'ZZ||||0|0'                                => 9,          // malformed: ignored
            'ZZ||||0'                                  => 2,
        ]);
        $this->assertSame(17, $g['total']);
        $this->assertSame(7, $g['near']);
        $this->assertSame(3, $g['andalucia']);
        $this->assertSame(14, $g['spain']);
        $this->assertSame(3, $g['abroad']);                      // Moldova 1 + hidden 2
        $this->assertSame('Málaga', $g['cities'][0]['city']);
        $this->assertSame('Ubicación oculta', collect($g['countries'])->firstWhere('cc', 'ZZ')['name']);
        if ($g['es']) {
            $this->assertSame(10, array_sum(array_intersect_key($g['regions'], array_flip(['01']))));
            [$x, $y] = StatsInsights::esXy(36.72, -4.42, $g['es']);
            $this->assertEqualsWithDelta(217.1, $x, 0.2);           // where the map was drawn to put Málaga
            $this->assertEqualsWithDelta(406.4, $y, 0.2);
        }
    }

    public function test_the_wider_map_comes_with_the_first_visitor_from_abroad_and_frames_wherever_they_are(): void
    {
        $spain = ['ES|Andalusia|Málaga|36.72|-4.42' => 40, 'ES|Madrid|Madrid|40.42|-3.7' => 12,
            'ES|Canary Islands|Telde|27.99|-15.42' => 2,
            'ES|Andalucia Oriental|Granada|37.18|-3.6' => 5];             // a region name nobody spells: read off the map
        $g = StatsInsights::places($spain);
        if (! $g['es']) {
            $this->markTestSkipped('no map geometry');
        }
        $this->assertNull($g['wide']);                                    // only Spain: the Spain map says it all
        $this->assertSame(45, $g['regions']['01']);
        $this->assertSame(5, $g['andalucia']);
        $this->assertSame(['Málaga', 'Madrid', 'Granada', 'Telde'], array_column($g['spainMap']['dots'], 'name'));

        $inside = function (array $w) {
            foreach ($w['dots'] as $d) {
                $this->assertTrue($d['x'] >= 0 && $d['x'] <= $w['w'] && $d['y'] >= 0 && $d['y'] <= $w['h'], json_encode($d));
            }
            foreach ($w['names'] as $p) {
                $this->assertTrue($p['y'] - 19 >= 0 && $p['y'] <= $w['h']);
            }
        };

        // next door: the Peninsula, Gibraltar's dot although it is too small to draw
        $near = StatsInsights::places($spain + ['GI|Gibraltar|Gibraltar|36.14|-5.35' => 3, 'PT|Lisbon|Lisboa|38.72|-9.14' => 2])['wide'];
        $inside($near);
        $this->assertLessThan(15, count($near['lands']));
        $this->assertContains('Gibraltar', array_column($near['names'], 'name'));
        $this->assertContains('Lisboa', array_column($near['names'], 'name'));    // close up: towns by name
        $this->assertSame(['Portugal'], array_values(array_column(array_filter($near['lands'], fn ($l) => $l['o'] && $l['name'] !== 'España'), 'name')));

        // far: the frame opens to take them in, and names countries
        $far = StatsInsights::places($spain + ['AU|New South Wales|Sydney|-33.87|151.21' => 1, 'US|California|Los Angeles|34.05|-118.24' => 2])['wide'];
        $inside($far);
        $this->assertGreaterThan(150, count($far['lands']));
        $this->assertSame(['Estados Unidos', 'Australia', 'Málaga'], array_column($far['names'], 'name'));
        $this->assertCount(1, array_filter($far['dots'], fn ($d) => str_starts_with($d['title'], 'Málaga y')));  // Spain is one dot from that far

        // a country known without a town is framed by its main body and coloured, without a dot
        $fr = StatsInsights::places($spain + ['FR||||0' => 2])['wide'];
        $this->assertNotNull($fr);
        $this->assertNotNull(collect($fr['lands'])->firstWhere('name', 'Francia')['o']);
        $this->assertCount(0, array_filter($fr['dots'], fn ($d) => str_contains($d['title'], 'Francia')));
        $this->assertLessThan(80, count($fr['lands']));                   // France, not French Guiana

        // nobody from Spain: the wider map alone, Spain still in the frame
        $only = StatsInsights::places(['DE|Hesse|Frankfurt am Main|50.11|8.68' => 3, 'ZZ||||0' => 1]);
        $this->assertNull($only['spainMap']);
        $this->assertNotNull($only['wide']);
        $this->assertNotNull(collect($only['wide']['lands'])->firstWhere('name', 'España'));

        // hidden only: nothing to draw
        $this->assertNull(StatsInsights::places(['ZZ||||0' => 4])['wide']);
    }

    public function test_one_cars_photographs_one_by_one(): void
    {
        $car = Vehicle::where('slug', 'bmw-318d-2020')->first();
        $k = array_column(StatsInsights::photoList($car), 'k');
        $this->assertCount(3, $k);                               // the cover is also in the gallery: once
        $this->row('timen', '/coche/bmw-318d-2020', 10);
        $this->row('pic', 'bmw-318d-2020|' . $k[0], 10);
        $this->row('pict', 'bmw-318d-2020|' . $k[0], 45);
        $this->row('pic', 'bmw-318d-2020|' . $k[1], 4);
        $this->row('pict', 'bmw-318d-2020|' . $k[1], 20);
        $this->row('picz', 'bmw-318d-2020|' . $k[1], 2);
        $this->row('pic', 'bmw-x1-2017|' . $k[2], 99);           // another car's: not this one's

        $p = StatsInsights::carPhotos($car, '2026-10-01', '2026-10-24');
        $this->assertSame(10, $p['views']);
        $this->assertSame([100, 40, 0], array_column($p['list'], 'pct'));
        $this->assertSame([5, 5, null], array_column($p['list'], 'sec'));
        $this->assertSame([0, 2, 0], array_column($p['list'], 'big'));

        $all = StatsInsights::photos($this->by());
        $this->assertSame(10, $all['views']);
        $this->assertSame(1.4, $all['avg']);
        $this->assertSame([100, 40, 0], array_column($all['reach'], 'pct'));
    }

    public function test_the_log_estimate_counts_rendering_browsers_and_leaves_out_robots_and_the_team(): void
    {
        $log = sys_get_temp_dir() . '/mc-stats-history-test.log';
        $l = fn ($ip, $time, $req, $status, $ref, $ua) => sprintf('%s - - [%s +0200] "%s HTTP/2.0" %d 100 "%s" "%s"', $ip, $time, $req, $status, $ref, $ua);
        $bot = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';
        file_put_contents($log, implode("\n", [
            // a person on a phone: the page, then the manifest it links; a reload; a car; a search
            $l('83.56.1.1', '20/Oct/2026:10:00:00', 'GET /', 200, 'https://www.google.com/', self::IPHONE),
            $l('83.56.1.1', '20/Oct/2026:10:00:01', 'GET /manifest.json', 200, 'https://ivmotorclass.com/', self::IPHONE),
            $l('83.56.1.1', '20/Oct/2026:10:00:05', 'GET /', 200, '-', self::IPHONE),
            $l('83.56.1.1', '20/Oct/2026:10:01:00', 'GET /coche/bmw-318d-2020', 200, 'https://ivmotorclass.com/', self::IPHONE),
            $l('83.56.1.1', '20/Oct/2026:10:02:00', 'GET /catalogo?marca=bmw', 200, 'https://ivmotorclass.com/', self::IPHONE),
            // a robot that says it is Chrome and reads only HTML
            $l('45.1.1.1', '20/Oct/2026:11:00:00', 'GET /coche/bmw-318d-2020', 200, '-', $bot),
            // the owner: signs in from this line, so all of it is his
            $l('88.6.6.6', '20/Oct/2026:12:00:00', 'GET /coche/vw-golf-2016', 200, '-', self::IPHONE),
            $l('88.6.6.6', '20/Oct/2026:12:00:01', 'GET /manifest.json', 200, 'https://ivmotorclass.com/coche/vw-golf-2016', self::IPHONE),
            $l('88.6.6.6', '20/Oct/2026:12:05:00', 'GET /admin', 302, '-', self::IPHONE),
            // after the beacon took over: not read
            $l('83.56.1.2', '24/Oct/2026:19:00:00', 'GET /', 200, '-', self::IPHONE),
            $l('83.56.1.2', '24/Oct/2026:19:00:01', 'GET /manifest.json', 200, 'https://ivmotorclass.com/', self::IPHONE),
        ]) . "\n");

        $r = StatsHistory::import([$log]);
        @unlink($log);
        $this->assertSame(1, $r['people']);
        $h = fn ($m, $k = '') => (int) DB::table('stats')->where('metric', 'h.' . $m)->where('key', $k)->sum('n');
        $this->assertSame(3, $h('views'));                       // /, the car, the catalogue — the reload is not a view
        $this->assertSame(1, $h('carp', 'bmw-318d-2020'));
        $this->assertSame(0, $h('carp', 'vw-golf-2016'));        // the owner's
        $this->assertSame(1, $h('source', 'google'));
        $this->assertSame(1, $h('search', 'marca:bmw'));

        // kept apart: the real counting has not started
        $this->assertNull(Stats::firstDay());
        $this->assertNull(Stats::perCar(30));
        $hist = StatsInsights::history();
        $this->assertSame(1, $hist['people']);
        $this->assertSame('2026-10-20', $hist['from']);
        $this->assertSame([['name' => 'Seat', 'n' => 1, 'sum' => 5000, 'avg' => 5000]], $hist['sold']);

        // and it can be run again without doubling
        file_put_contents($log, $l('83.56.1.1', '20/Oct/2026:10:00:00', 'GET /', 200, '-', self::IPHONE) . "\n"
            . $l('83.56.1.1', '20/Oct/2026:10:00:01', 'GET /manifest.json', 200, 'https://ivmotorclass.com/', self::IPHONE) . "\n");
        StatsHistory::import([$log]);
        @unlink($log);
        $this->assertSame(1, $h('views'));
    }

    public function test_the_pages_carry_the_hooks_and_the_panel_shows_the_new_parts(): void
    {
        $car = $this->get('/coche/bmw-318d-2020')->assertOk()->getContent();
        $k = Stats::photoKey('/storage/vehicles/bmw-318d-2020/a.jpg');
        $this->assertStringContainsString('data-k="' . $k . '"', $car);
        $this->assertStringContainsString('data-stat-gallery', $car);
        $this->assertStringContainsString('mcStatPic(', $car);

        $cat = $this->get('/catalogo')->assertOk()->getContent();
        $this->assertStringContainsString('data-stat="marca-abrir" data-stat-k="bmw"', $cat);
        $this->assertStringContainsString('data-stat-seen="marca-vista" data-stat-k="bmw"', $cat);

        $site = $this->get('/')->getContent();
        $this->assertStringContainsString('navigator.webdriver', $site);
        $this->assertStringContainsString('prerenderingchange', $site);

        $this->withServerVariables(['REMOTE_ADDR' => '83.56.1.1'])
            ->withHeaders(['User-Agent' => self::IPHONE])->post('/s', ['t' => 'view', 'p' => '/coche/bmw-318d-2020']);
        $this->withServerVariables(['REMOTE_ADDR' => '95.1.2.3']);
        $u = User::factory()->create();
        $this->actingAs($u)->get('/admin/estadisticas')->assertOk()
            ->assertSee('Qué marcas atraen')->assertSee('Qué precio miran')->assertSee('De dónde son')
            ->assertSee('Las fotos')->assertSee('Tus visitas, aparte')->assertSee('/no-contar?signature=', false);
        $this->actingAs($u)->get('/admin/estadisticas?coche=bmw-318d-2020')->assertOk()->assertSee('Sus fotos');
    }
}
