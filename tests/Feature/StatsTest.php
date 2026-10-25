<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Support\Stats;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0.1 Mobile/15E148 Safari/604.1';
    private const PC = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-24 12:00:00', Stats::TZ));
        Vehicle::create(['slug' => 'bmw-318d-2020', 'brand' => 'BMW', 'model' => '318d', 'title' => 'x', 'year' => 2020,
            'price' => 21900, 'mileage' => 90000, 'status' => 'available']);
    }

    private function beacon(array $data, string $ua = self::IPHONE, string $ip = '83.44.1.2', array $headers = [])
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->withHeaders(['User-Agent' => $ua] + $headers)
            ->post('/s', $data);
    }

    /** $n photograph keys from $from on: a0000000, a0000001… */
    private static function pics(int $from, int $n): string
    {
        return implode(',', array_map(fn ($i) => sprintf('a%07d', $i), range($from, $from + $n - 1)));
    }

    private function n(string $metric, string $key = ''): int
    {
        return (int) DB::table('stats')->where('metric', $metric)->where('key', $key)->sum('n');
    }

    public function test_a_page_counts_a_person_a_visit_and_where_they_came_from(): void
    {
        $this->beacon(['t' => 'view', 'p' => '/', 'r' => 'https://www.google.es/'])->assertNoContent();
        $this->travel(40)->seconds();
        $this->beacon(['t' => 'view', 'p' => '/catalogo', 'r' => 'https://ivmotorclass.com/']);

        $this->assertSame(1, $this->n('people'));
        $this->assertSame(1, $this->n('visits'));
        $this->assertSame(2, $this->n('views'));
        $this->assertSame(1, $this->n('source', 'google'));
        $this->assertSame(1, $this->n('device', 'mobile'));
        $this->assertSame(1, $this->n('page', '/'));
        $this->assertSame(1, $this->n('page', '/catalogo'));

        $v = DB::table('stat_visits')->first();
        $this->assertSame('iOS', $v->os);
        $this->assertSame(2, (int) $v->pages);
        $this->assertSame(['/', '/catalogo'], array_column(json_decode($v->steps, true), 'p'));
        $this->assertSame(40, json_decode($v->steps, true)[1]['t']);
        // Nothing that identifies anyone is stored.
        $this->assertStringNotContainsString('83.44.1.2', json_encode(DB::table('stat_seen')->get()));
    }

    public function test_a_reload_is_not_a_second_view_and_half_an_hour_of_silence_ends_a_visit(): void
    {
        $this->beacon(['t' => 'view', 'p' => '/']);
        $this->travel(5)->seconds();
        $this->beacon(['t' => 'view', 'p' => '/']);
        $this->assertSame(1, $this->n('views'));

        $this->travel(31)->minutes();
        $this->beacon(['t' => 'view', 'p' => '/', 'r' => 'android-app://com.whatsapp/']);
        $this->assertSame(2, $this->n('views'));
        $this->assertSame(1, $this->n('people'));
        $this->assertSame(2, $this->n('visits'));
        $this->assertSame(1, $this->n('source', 'whatsapp'));
        $this->assertSame(2, DB::table('stat_visits')->count());
    }

    public function test_robots_the_server_the_owner_and_other_sites_are_not_counted(): void
    {
        $this->beacon(['t' => 'view', 'p' => '/'], 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $this->beacon(['t' => 'view', 'p' => '/'], 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/120.0 Safari/537.36');
        $this->beacon(['t' => 'view', 'p' => '/'], 'curl/8.5.0');
        $this->beacon(['t' => 'view', 'p' => '/'], self::IPHONE, '127.0.0.1');
        $this->beacon(['t' => 'view', 'p' => '/'], self::IPHONE, '83.44.1.3', ['Sec-Fetch-Site' => 'cross-site']);
        $this->beacon(['t' => 'view', 'p' => '/wp-login.php']);
        $this->beacon(['t' => 'view', 'p' => '/coche/no-existe']);
        $this->actingAs(User::factory()->create());
        $this->beacon(['t' => 'view', 'p' => '/'], self::PC, '83.44.1.9')->assertNoContent();

        $this->assertSame(0, DB::table('stats')->count());
    }

    public function test_a_car_counts_views_people_and_its_sources_and_the_placard_qr(): void
    {
        $this->beacon(['t' => 'view', 'p' => '/coche/bmw-318d-2020', 'q' => '?c=qr', 'r' => '']);
        $this->travel(30)->seconds();
        $this->beacon(['t' => 'view', 'p' => '/']);
        $this->travel(30)->seconds();
        $this->beacon(['t' => 'view', 'p' => '/coche/bmw-318d-2020']);
        $this->beacon(['t' => 'view', 'p' => '/coche/bmw-318d-2020', 'r' => 'https://www.instagram.com/'], self::PC, '90.1.1.1');

        $this->assertSame(3, $this->n('car', 'bmw-318d-2020'));
        $this->assertSame(2, $this->n('carp', 'bmw-318d-2020'));
        $this->assertSame(1, $this->n('qr', 'bmw-318d-2020'));
        $this->assertSame(1, $this->n('source', 'cartel'));
        $this->assertSame(1, $this->n('carsrc', 'bmw-318d-2020|cartel'));
        $this->assertSame(1, $this->n('carsrc', 'bmw-318d-2020|instagram'));
    }

    public function test_presses_time_scroll_and_photos(): void
    {
        // A press from someone who never loaded a page is not counted.
        $this->beacon(['t' => 'wa', 'p' => '/coche/bmw-318d-2020']);
        $this->assertSame(0, $this->n('wa', '/coche/bmw-318d-2020'));

        $this->beacon(['t' => 'view', 'p' => '/coche/bmw-318d-2020']);
        $this->beacon(['t' => 'ev', 'e' => 'galeria', 'p' => '/coche/bmw-318d-2020']);
        $this->beacon(['t' => 'ev', 'e' => 'NOT valid!', 'p' => '/coche/bmw-318d-2020']);
        $this->beacon(['t' => 'eng', 'p' => '/coche/bmw-318d-2020', 's' => 70, 'y' => 60, 'y0' => 0, 'f' => '1',
            'pv' => self::pics(0, 9), 'pt' => 'a0000000:12,a0000001:3', 'pz' => 'a0000000']);
        $this->beacon(['t' => 'eng', 'p' => '/coche/bmw-318d-2020', 's' => 50, 'y' => 100, 'y0' => 50, 'f' => '0',
            'pv' => self::pics(9, 5) . ',zzz,../x', 'pt' => 'a0000000:4,bad:9', 'ph' => 99]);
        $this->beacon(['t' => 'eng', 'p' => '/coche/bmw-318d-2020', 's' => 99999, 'y' => 100, 'y0' => 100, 'f' => '0']);
        $this->beacon(['t' => 'wa', 'p' => '/coche/bmw-318d-2020']);
        $this->beacon(['t' => 'tel', 'p' => '/coche/bmw-318d-2020']);

        $path = '/coche/bmw-318d-2020';
        $this->assertSame(1, $this->n('ev', 'galeria|' . $path));
        $this->assertSame(1, (int) DB::table('stats')->where('metric', 'ev')->count());
        $this->assertSame(1, $this->n('timen', $path));
        $this->assertSame(70 + 50 + Stats::MAX_SECONDS, $this->n('time', $path));
        $this->assertSame(1, $this->n('s75', $path));
        $this->assertSame(1, $this->n('s100', $path));
        $this->assertSame(14, $this->n('photos', 'bmw-318d-2020'));
        $this->assertSame(1, $this->n('wa', $path));
        $this->assertSame(1, $this->n('tel', $path));
        // each photograph: seen in how many page views, for how long, opened big
        $this->assertSame(14, (int) DB::table('stats')->where('metric', 'pic')->sum('n'));
        $this->assertSame(1, $this->n('pic', 'bmw-318d-2020|a0000000'));
        $this->assertSame(16, $this->n('pict', 'bmw-318d-2020|a0000000'));
        $this->assertSame(3, $this->n('pict', 'bmw-318d-2020|a0000001'));
        $this->assertSame(1, $this->n('picz', 'bmw-318d-2020|a0000000'));

        $v = DB::table('stat_visits')->first();
        $this->assertTrue((bool) $v->wa);
        $step = json_decode($v->steps, true)[0];
        $this->assertSame(14, $step['ph']);
        $this->assertSame(['galeria', 'wa', 'tel'], $step['e']);

        $car = Stats::car(Vehicle::first());
        $this->assertSame(1, $car['w7']['views']);
        $this->assertSame(1, $car['w7']['wa']);
        $this->assertSame(14.0, $car['w7']['photos']);
        $this->assertSame(100, $car['w7']['end']);
    }

    public function test_the_home_search_is_kept_as_what_people_look_for(): void
    {
        $this->beacon(['t' => 'view', 'p' => '/catalogo', 'q' => '?marca=bmw&modelo=Serie%203&max=20000']);
        $this->assertSame(1, $this->n('search', 'marca:bmw'));
        $this->assertSame(1, $this->n('search', 'modelo:serie 3'));
        $this->assertSame(1, $this->n('search', 'max:20000'));
    }

    public function test_sources(): void
    {
        $this->assertSame('google', Stats::source('https://www.google.com/', self::IPHONE));
        $this->assertSame('buscadores', Stats::source('https://duckduckgo.com/', self::IPHONE));
        $this->assertSame('instagram', Stats::source('', self::IPHONE . ' Instagram 300.0'));
        $this->assertSame('facebook', Stats::source('https://l.facebook.com/', self::IPHONE));
        $this->assertSame('whatsapp', Stats::source('android-app://com.whatsapp/', self::IPHONE));
        $this->assertSame('cochesnet', Stats::source('https://www.coches.net/x', self::IPHONE));
        $this->assertSame('directo', Stats::source('https://ivmotorclass.com/catalogo', self::IPHONE, 'ivmotorclass.com'));
        $this->assertSame('otras', Stats::source('https://example.org/', self::IPHONE));
        $this->assertSame('directo', Stats::source('', self::IPHONE));
    }

    public function test_now_the_feed_and_the_lists_before_and_after_counting(): void
    {
        $this->assertNull(Stats::perCar(30));

        $this->beacon(['t' => 'view', 'p' => '/', 'r' => 'https://www.instagram.com/']);
        $this->travel(20)->seconds();
        $this->beacon(['t' => 'view', 'p' => '/coche/bmw-318d-2020']);
        $this->beacon(['t' => 'eng', 'p' => '/coche/bmw-318d-2020', 's' => 95, 'y' => 100, 'y0' => 0, 'pv' => self::pics(0, 6), 'f' => '1']);
        $this->beacon(['t' => 'wa', 'p' => '/coche/bmw-318d-2020']);

        $this->assertSame(['bmw-318d-2020' => ['views' => 1, 'wa' => 1]], Stats::perCar(30));

        $live = Stats::live();
        $this->assertSame(1, $live['n']);
        $this->assertSame('BMW 318d 2020', $live['where'][0]['label']);

        $this->travel(6)->minutes();
        $this->assertSame(0, Stats::live()['n']);

        $s = Stats::summary(7);
        $v = $s['visitsFeed'][0];
        $this->assertSame('iPhone', $v['who']);
        $this->assertSame('Instagram', $v['source']);
        $this->assertTrue($v['wa']);
        $this->assertSame(['Inicio', 'BMW 318d 2020'], array_column($v['steps'], 'label'));
        $this->assertSame(95, $v['steps'][1]['d']);
        $this->assertSame(6, $v['steps'][1]['ph']);
        $this->assertSame(1, $s['cars'][0]['views']);
        $this->assertSame(95, $s['cars'][0]['time']);
        $this->assertSame(100, $s['cars'][0]['end']);
    }

    public function test_garbage_gets_a_204_too(): void
    {
        $this->post('/s', ['t' => 'x'])->assertNoContent();
        $this->post('/s')->assertNoContent();
    }

    public function test_old_rows_are_pruned(): void
    {
        $this->beacon(['t' => 'view', 'p' => '/']);
        $this->travel(40)->days();
        Stats::prune();
        $this->assertSame(0, DB::table('stat_seen')->count());
        $this->assertSame(0, DB::table('stat_visits')->count());
        $this->assertSame(1, $this->n('views'));
    }

    public function test_the_panel_pages(): void
    {
        $this->get('/admin/estadisticas')->assertRedirect();

        $this->beacon(['t' => 'view', 'p' => '/', 'r' => 'https://www.google.es/']);
        $this->beacon(['t' => 'view', 'p' => '/coche/bmw-318d-2020']);
        $this->beacon(['t' => 'wa', 'p' => '/coche/bmw-318d-2020']);

        $u = User::factory()->create();
        // the owner signs in from his own connection; the visitor's stays counted
        $this->withServerVariables(['REMOTE_ADDR' => '95.1.2.3']);
        $this->actingAs($u)->get('/admin/estadisticas')->assertOk()
            ->assertSee('Estadísticas')->assertSee('BMW 318d')->assertSee('Google');
        $this->actingAs($u)->get('/admin/estadisticas?d=7&frag=1')->assertOk()
            ->assertDontSee('<html', false)->assertSee('BMW 318d');
        $this->actingAs($u)->get('/admin/estadisticas?coche=bmw-318d-2020')->assertOk()->assertSee('BMW 318d');
        $this->actingAs($u)->get('/admin/estadisticas?coche=nope')->assertNotFound();

        $this->actingAs($u)->get('/admin')->assertOk()->assertSee('Ver estadísticas')
            ->assertSee('1 vista · 1 WhatsApp');
        $this->actingAs($u)->get('/admin/vehicles')->assertOk()->assertSee('1 WhatsApp', false);
        $this->actingAs($u)->get(route('admin.vehicles.edit', 'bmw-318d-2020'))->assertOk()
            ->assertSee('En la web')->assertSee('/admin/estadisticas?coche=bmw-318d-2020', false);
    }

    /* ---- 25.10: the owner, uncounted whether signed in or not ------------- */

    public function test_the_owner_looking_before_signing_in_is_taken_out_again(): void
    {
        $car = '/coche/bmw-318d-2020';
        // a real visitor elsewhere
        $this->beacon(['t' => 'view', 'p' => '/'], self::PC, '90.1.1.1');
        // the owner, not signed in yet, on his home line: a page, time, photographs, WhatsApp, a search
        $this->beacon(['t' => 'view', 'p' => '/catalogo', 'q' => '?marca=bmw'], self::IPHONE, '83.44.1.2');
        $this->beacon(['t' => 'view', 'p' => $car], self::IPHONE, '83.44.1.2');
        $this->beacon(['t' => 'eng', 'p' => $car, 's' => 40, 'y' => 100, 'y0' => 0, 'f' => '1', 'pv' => self::pics(0, 3), 'pt' => 'a0000000:9'], self::IPHONE, '83.44.1.2');
        $this->beacon(['t' => 'wa', 'p' => $car], self::IPHONE, '83.44.1.2');
        // and on the same line, another browser (WhatsApp's own, say)
        $this->beacon(['t' => 'view', 'p' => $car], self::PC, '83.44.1.2');
        $this->assertSame(3, $this->n('people'));

        // then he signs in, from that line
        $this->withServerVariables(['REMOTE_ADDR' => '83.44.1.2'])
            ->actingAs(User::factory()->create())->get('/admin')->assertOk()
            ->assertCookie(Stats::STAFF_COOKIE);

        // only the real visitor is left, in every number
        $this->assertSame(1, $this->n('people'));
        $this->assertSame(1, $this->n('views'));
        $this->assertSame(1, $this->n('visits'));
        $this->assertSame(1, $this->n('page', '/'));
        foreach (['car', 'carp', 'wa', 'wap', 'timen', 'time', 'photos', 'pic', 'pict', 'search', 'carsrc'] as $m) {
            $this->assertSame(0, (int) DB::table('stats')->where('metric', $m)->sum('n'), $m);
        }
        $this->assertSame(1, DB::table('stat_visits')->where('team', false)->count());
        $this->assertSame(2, DB::table('stat_visits')->where('team', true)->count());   // his, kept as "Tus visitas"
        $this->assertSame(1, DB::table('stat_seen')->count());

        // and for the next hours, nothing from that line counts — signed in or not
        auth()->logout();
        $this->travel(2)->hours();
        $this->beacon(['t' => 'view', 'p' => '/'], self::IPHONE, '83.44.1.2');
        $this->assertSame(1, $this->n('people'));
        // after that, the line is anybody's again
        $this->travel(Stats::STAFF_IP + 60)->seconds();
        $this->beacon(['t' => 'view', 'p' => '/contacto'], self::IPHONE, '83.44.1.2');
        $this->assertSame(1, $this->n('page', '/contacto'));
    }

    public function test_a_device_of_the_team_never_counts_on_any_connection(): void
    {
        $this->withCookie(Stats::STAFF_COOKIE, '1');
        $this->beacon(['t' => 'view', 'p' => '/'], self::IPHONE, '88.1.1.1');
        $this->beacon(['t' => 'view', 'p' => '/'], self::IPHONE, '176.2.2.2');
        $this->assertSame(0, DB::table('stats')->count());
    }

    public function test_the_no_count_link_marks_a_phone_and_can_be_undone(): void
    {
        $this->get('/no-contar')->assertForbidden();               // only the signed link
        $r = $this->withServerVariables(['REMOTE_ADDR' => '88.9.9.9'])->get(\URL::signedRoute('stats.me'));
        $r->assertOk()->assertSee('Este dispositivo ya no cuenta')->assertCookie(Stats::STAFF_COOKIE, '1');
        $this->assertTrue(Stats::staffIp('88.9.9.9'));

        $u = $this->withServerVariables(['REMOTE_ADDR' => '88.9.9.9'])->get(\URL::signedRoute('stats.me.undo'));
        $u->assertOk()->assertSee('vuelve a contar')->assertCookieExpired(Stats::STAFF_COOKIE);
        $this->assertFalse(Stats::staffIp('88.9.9.9'));
    }

    public function test_presses_and_searches_count_people_not_presses(): void
    {
        $car = '/coche/bmw-318d-2020';
        $this->beacon(['t' => 'view', 'p' => '/catalogo', 'q' => '?marca=bmw']);
        $this->travel(15)->seconds();
        $this->beacon(['t' => 'view', 'p' => '/catalogo', 'q' => '?marca=bmw']);
        $this->beacon(['t' => 'view', 'p' => $car]);
        foreach ([1, 2, 3] as $i) {
            $this->beacon(['t' => 'wa', 'p' => $car]);
            $this->beacon(['t' => 'ev', 'e' => 'galeria', 'p' => $car]);
        }
        $this->beacon(['t' => 'wa', 'p' => '/catalogo']);
        $this->beacon(['t' => 'ev', 'e' => 'marca-abrir', 'k' => 'bmw', 'p' => '/catalogo']);
        $this->beacon(['t' => 'ev', 'e' => 'marca-abrir', 'k' => 'BMW!', 'p' => '/catalogo']);

        $this->assertSame(1, $this->n('search', 'marca:bmw'));
        $this->assertSame(1, $this->n('wa', $car));
        $this->assertSame(1, $this->n('wa', '/catalogo'));
        $this->assertSame(1, $this->n('wap'));                    // one person pressed WhatsApp
        $this->assertSame(1, $this->n('ev', 'galeria|' . $car));
        $this->assertSame(1, $this->n('evk', 'marca-abrir|bmw'));
        $this->assertSame(1, (int) DB::table('stats')->where('metric', 'evk')->count());
    }

    public function test_the_day_salt_survives_a_cache_clear(): void
    {
        $this->beacon(['t' => 'view', 'p' => '/']);
        \Illuminate\Support\Facades\Cache::flush();
        $this->travel(20)->seconds();
        $this->beacon(['t' => 'view', 'p' => '/catalogo']);
        $this->beacon(['t' => 'wa', 'p' => '/catalogo']);
        $this->assertSame(1, $this->n('people'));
        $this->assertSame(1, $this->n('visits'));
        $this->assertSame(1, $this->n('wa', '/catalogo'));
    }

    public function test_a_page_left_after_midnight_still_reports_on_its_own_day(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-24 23:58:00', Stats::TZ));
        $this->beacon(['t' => 'view', 'p' => '/coche/bmw-318d-2020']);
        $this->travelTo(CarbonImmutable::parse('2026-10-25 00:04:00', Stats::TZ));
        $this->beacon(['t' => 'eng', 'p' => '/coche/bmw-318d-2020', 's' => 300, 'y' => 50, 'y0' => 0, 'f' => '1']);
        $this->beacon(['t' => 'wa', 'p' => '/coche/bmw-318d-2020']);

        $day = fn (string $m, string $k) => DB::table('stats')->where('metric', $m)->where('key', $k)->pluck('n', 'day')->all();
        $this->assertSame(['2026-10-24' => 300], $day('time', '/coche/bmw-318d-2020'));
        $this->assertSame(['2026-10-24' => 1], $day('wa', '/coche/bmw-318d-2020'));

        // an hour later it is a new day's visit, not yesterday's
        $this->travelTo(CarbonImmutable::parse('2026-10-25 01:10:00', Stats::TZ));
        $this->beacon(['t' => 'wa', 'p' => '/coche/bmw-318d-2020']);
        $this->assertSame(['2026-10-24' => 1], $day('wa', '/coche/bmw-318d-2020'));
    }

    public function test_rendering_robots_and_automated_browsers_are_not_counted(): void
    {
        foreach ([
            'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 (compatible; Google-InspectionTool/1.0;)',
            'Mozilla/5.0 (Linux; Android 11; moto g power (2022)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Mobile Safari/537.36 Chrome-Lighthouse',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 PTST/240101.120000',
            'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)',
            'Mozilla/5.0 (compatible; AhrefsSiteAudit/6.1; +http://ahrefs.com/robot/site-audit)',
        ] as $ua) {
            $this->beacon(['t' => 'view', 'p' => '/'], $ua);
        }
        $this->assertSame(0, DB::table('stats')->count());
    }

    public function test_where_a_visitor_is_from_when_the_town_database_is_there(): void
    {
        if (! is_file(\App\Support\Geo::path())) {
            $this->markTestSkipped('no GeoIP database on this machine');
        }
        $g = \App\Support\Geo::find('83.56.1.1');
        $this->assertSame('ES', $g['cc']);
        $this->beacon(['t' => 'view', 'p' => '/'], self::IPHONE, '83.56.1.1');
        $this->assertSame(1, (int) DB::table('stats')->where('metric', 'geo')->where('key', 'like', 'ES|%')->sum('n'));
        $this->assertNotSame('', DB::table('stat_visits')->value('place'));
        $this->assertNull(\App\Support\Geo::find('10.1.2.3'));
        $this->assertStringNotContainsString('83.56.1.1', json_encode(DB::table('stats')->get()) . json_encode(DB::table('stat_visits')->get()));
    }

    public function test_the_teams_visits_are_recorded_apart_and_never_counted(): void
    {
        $car = '/coche/bmw-318d-2020';
        // the owner's phone on the line where the panel is open
        \App\Support\Stats::markStaff('83.44.1.2');
        $this->beacon(['t' => 'view', 'p' => '/']);
        $this->travel(20)->seconds();
        $this->beacon(['t' => 'view', 'p' => $car]);
        $this->beacon(['t' => 'eng', 'p' => $car, 's' => 30, 'y' => 100, 'y0' => 0, 'f' => '1', 'pv' => self::pics(0, 4)]);
        $this->beacon(['t' => 'wa', 'p' => $car]);
        // and a real visitor
        $this->beacon(['t' => 'view', 'p' => '/catalogo'], self::PC, '90.1.1.1');

        // no counter has the team in it
        $this->assertSame(1, $this->n('people'));
        $this->assertSame(1, $this->n('views'));
        $this->assertSame(0, (int) DB::table('stats')->whereIn('metric', ['car', 'carp', 'wa', 'wap', 'photos', 'pic', 'timen'])->sum('n'));
        // nor "ahora en la web", nor the visitors' list
        $this->assertSame(1, Stats::live()['n']);
        $this->assertCount(1, Stats::feed(CarbonImmutable::now(Stats::TZ)->startOfDay(), 10));
        // but the team's own visit is there, whole
        $team = Stats::feed(CarbonImmutable::now(Stats::TZ)->startOfDay(), 10, null, true);
        $this->assertCount(1, $team);
        $this->assertSame(['Inicio', 'BMW 318d 2020'], array_column($team[0]['steps'], 'label'));
        $this->assertSame(4, $team[0]['steps'][1]['ph']);
        $this->assertSame(30, $team[0]['steps'][1]['d']);
        $this->assertTrue($team[0]['wa']);
    }
}
