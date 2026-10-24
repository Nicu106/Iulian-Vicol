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
        $this->beacon(['t' => 'eng', 'p' => '/coche/bmw-318d-2020', 's' => 70, 'y' => 60, 'y0' => 0, 'ph' => 9, 'f' => '1']);
        $this->beacon(['t' => 'eng', 'p' => '/coche/bmw-318d-2020', 's' => 50, 'y' => 100, 'y0' => 50, 'ph' => 5, 'f' => '0']);
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
        $this->assertSame(1, $this->n('search', 'modelo:Serie 3'));
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
        $this->beacon(['t' => 'eng', 'p' => '/coche/bmw-318d-2020', 's' => 95, 'y' => 100, 'y0' => 0, 'ph' => 6, 'f' => '1']);
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
}
