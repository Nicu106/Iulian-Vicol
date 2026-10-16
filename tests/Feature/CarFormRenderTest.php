<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Support\YouTube;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * What the car form and the car page draw (2026-10-16).
 *
 * With MC_DUMP=/some/dir set, the rendered HTML is also written there, so the
 * pages can be photographed in a browser without signing in to the live panel:
 * the admin is behind auth, and the car page is shown here with real cars read
 * from the live database READ-ONLY into this test's in-memory one.
 */
class CarFormRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        // The poster's still is chosen with a HEAD to i.ytimg.com; hermetic here,
        // except when dumping real cars for screenshots.
        if (!getenv('MC_DUMP') && $this->name() !== 'test_the_still_steps_down_to_one_that_exists_and_is_remembered') {
            Http::fake(['i.ytimg.com/*' => Http::response('', 200)]);
        }
    }

    public function test_the_still_steps_down_to_one_that_exists_and_is_remembered(): void
    {
        Http::fake([
            'i.ytimg.com/vi/aaaaaaaaaaa/maxresdefault.jpg' => Http::response('', 404),
            'i.ytimg.com/vi/aaaaaaaaaaa/sddefault.jpg'     => Http::response('', 200),
            'i.ytimg.com/vi/bbbbbbbbbbb/*'                 => fn () => throw new \Illuminate\Http\Client\ConnectionException('offline'),
        ]);
        $this->assertSame('https://i.ytimg.com/vi/aaaaaaaaaaa/sddefault.jpg', YouTube::still('aaaaaaaaaaa'));
        $this->assertSame('https://i.ytimg.com/vi/aaaaaaaaaaa/sddefault.jpg', YouTube::still('aaaaaaaaaaa'));
        Http::assertSentCount(2);   // the second answer came from the cache
        $this->assertSame('https://i.ytimg.com/vi/bbbbbbbbbbb/hqdefault.jpg', YouTube::still('bbbbbbbbbbb'), 'offline: the one that always exists');
    }

    private function dump(string $name, string $html): void
    {
        $dir = getenv('MC_DUMP');
        if ($dir && is_dir($dir)) {
            file_put_contents(rtrim($dir, '/') . '/' . $name . '.html', $html);
        }
    }

    private function car(array $attrs = []): Vehicle
    {
        $slug = $attrs['slug'] ?? 'audi-a4-avant-2018-qwert';
        $g = array_map(fn ($n) => "/storage/vehicles/$slug/$n.jpg", ['a', 'b', 'c', 'd', 'e']);

        return Vehicle::create(array_merge([
            'slug' => $slug, 'brand' => 'Audi', 'model' => 'A4 Avant', 'title' => 'Audi A4 Avant 2018',
            'year' => 2018, 'price' => 21900, 'mileage' => 98000, 'status' => 'available',
            'cover_image' => $g[0], 'gallery_images' => $g,
            'image_tags' => [$g[0] => 'exterior', $g[1] => 'exterior', $g[2] => 'interior', $g[4] => 'flaw'],
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ?si=x',
        ], $attrs));
    }

    /** Live rows, read-only, for photographs that exist. Skips if the file is absent. */
    private function liveCars(): bool
    {
        $file = '/var/www/motorclass-v2/database/database.sqlite';
        if (!getenv('MC_DUMP') || !is_readable($file)) {
            return false;
        }
        $pdo = new \PDO('sqlite:' . $file, null, null, [\PDO::SQLITE_ATTR_OPEN_FLAGS => \PDO::SQLITE_OPEN_READONLY]);
        $cols = array_flip(\Schema::getColumnListing('vehicles'));
        foreach ($pdo->query('select * from vehicles')->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            DB::table('vehicles')->insert(array_intersect_key($row, $cols));
        }

        return true;
    }

    public function test_the_add_form_offers_three_groups_and_the_session_hooks(): void
    {
        $html = $this->actingAs(User::factory()->create())->get('/admin/vehicles/create')->assertOk()->getContent();

        foreach (['exterior', 'interior', 'flaw'] as $g) {
            $this->assertMatchesRegularExpression('~<input type="file" name="photos_' . $g . '\[\]" accept="image/\*" multiple data-up="' . $g . '">~', $html);
        }
        $this->assertStringNotContainsString('name="photos_none', $html);
        $this->assertMatchesRegularExpression('~<section class="ad-cat" data-cat="none"\s+hidden\s*>~', $html, 'no unsorted group on a new car');
        $this->assertMatchesRegularExpression('~name="photo_token" id="ad-token" value="[A-Za-z0-9]{40}"~', $html);
        $this->assertStringContainsString('data-ping="' . route('admin.ping') . '"', $html);
        $this->assertStringContainsString('data-draft="mc-car:new"', $html);
        $this->assertStringContainsString('inputmode="numeric"', $html);
        $this->assertStringContainsString('Publicar el coche', $html);
        $this->dump('admin-create', $html);
    }

    public function test_the_edit_form_shows_each_photo_in_its_group_with_its_picker(): void
    {
        $car = $this->car();
        $g = $car->gallery_images;
        $html = $this->actingAs(User::factory()->create())->get('/admin/vehicles/' . $car->slug . '/edit')->assertOk()->getContent();

        // the unsorted one waits at the top, and the group shows
        $this->assertMatchesRegularExpression('~<section class="ad-cat" data-cat="none"\s*>~', $html);
        $order = array_map(fn ($c) => strpos($html, 'data-cat="' . $c . '"'), ['none', 'exterior', 'interior', 'flaw']);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);

        // each photo inside its own group's section
        foreach ([[$g[0], 'exterior'], [$g[1], 'exterior'], [$g[2], 'interior'], [$g[3], 'none'], [$g[4], 'flaw']] as [$p, $cat]) {
            $at = strpos($html, 'data-path="' . $p . '"');
            $sec = strrpos(substr($html, 0, $at), 'data-cat="');
            $this->assertSame('data-cat="' . $cat . '"', substr($html, $sec, strlen('data-cat="' . $cat . '"')), $p);
        }
        $this->assertSame(5, substr_count($html, '<select class="ad-tag__s" name="photos['));
        $this->assertMatchesRegularExpression('~<option value="flaw" selected>Desperfectos</option>~', $html);
        // the cover is checked; the video is previewed from its id
        $this->assertMatchesRegularExpression('~name="cover_from" value="' . preg_quote($g[0], '~') . '" checked~', $html);
        $this->assertStringContainsString('https://i.ytimg.com/vi/dQw4w9WgXcQ/mqdefault.jpg', $html);
        $this->assertStringContainsString('data-draft="mc-car:' . $car->slug . '"', $html);
        $this->assertStringContainsString('value="21900"', $html, 'the price without ".00" for the number pad');
        $this->dump('admin-edit-fake', $html);
    }

    public function test_dump_real_cars_for_screenshots(): void
    {
        if (!$this->liveCars()) {
            $this->markTestSkipped('MC_DUMP not set');
        }
        $u = User::factory()->create();
        // a car with photos already sorted and a video; one with a video and none sorted
        foreach (['mercedes-benz-e350d-bluetec-9g-2016-pg9d3', 'mercedes-cla-200d-amg-7g-2018-kiqk3', 'bmw-x1-sdrive-18d-xline-2017-dni02'] as $slug) {
            $this->dump('admin-edit-' . substr($slug, 0, 14), $this->actingAs($u)->get('/admin/vehicles/' . $slug . '/edit')->assertOk()->getContent());
            \Auth::logout();
            $this->dump('coche-' . substr($slug, 0, 14), $this->get('/coche/' . $slug)->assertOk()->getContent());
        }
        $none = Vehicle::whereIn('status', ['available'])->where(fn ($q) => $q->whereNull('video_url')->orWhere('video_url', ''))->first();
        if ($none) {
            $this->dump('coche-novideo', $this->get('/coche/' . $none->slug)->assertOk()->getContent());
            file_put_contents(rtrim(getenv('MC_DUMP'), '/') . '/novideo-slug.txt', $none->slug);
        }
        $this->assertTrue(true);
    }

    // ---- the car page ------------------------------------------------------

    public function test_the_car_page_shows_a_poster_and_no_youtube_until_pressed(): void
    {
        $car = $this->car(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=90s']);
        $html = $this->get('/coche/' . $car->slug)->assertOk()->getContent();

        $this->assertStringContainsString('class="car-video', $html);
        $this->assertStringContainsString('data-yt="dQw4w9WgXcQ"', $html);
        $this->assertStringContainsString('https://i.ytimg.com/vi/dQw4w9WgXcQ/maxresdefault.jpg', $html);
        $this->assertStringContainsString('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1&amp;rel=0&amp;playsinline=1&amp;start=90', $html);
        // nothing from YouTube runs before the press: no iframe, no script, no nocookie src
        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertDoesNotMatchRegularExpression('~<script[^>]+youtube~i', $html);
        $this->assertDoesNotMatchRegularExpression('~src="https://www\.youtube~', $html);
        // without JavaScript the poster is a link to the video
        $this->assertStringContainsString('href="https://www.youtube.com/watch?v=dQw4w9WgXcQ&amp;t=90s"', $html);
    }

    public function test_a_car_without_a_video_has_no_video_block(): void
    {
        foreach ([null, '', 'https://vimeo.com/1234'] as $i => $url) {
            $car = $this->car(['slug' => 'audi-a4-avant-2018-nov' . $i, 'video_url' => $url]);
            $html = $this->get('/coche/' . $car->slug)->assertOk()->getContent();
            $this->assertStringNotContainsString('car-video', $html);
            $this->assertStringNotContainsString('ytimg', $html);
        }
    }

    public function test_the_tabs_count_each_group_and_the_cover_is_not_counted_twice(): void
    {
        $car = $this->car(['video_url' => null]);
        $html = $this->get('/coche/' . $car->slug)->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~data-group="all">Todas <span>5</span>~', $html, 'the cover is gallery[0]: five photos, not six');
        $this->assertMatchesRegularExpression('~data-group="exterior">Exterior <span>2</span>~', $html);
        $this->assertMatchesRegularExpression('~data-group="interior">Interior <span>1</span>~', $html);
        $this->assertMatchesRegularExpression('~data-group="flaw">Imperfecciones <span>1</span>~', $html);
    }

    public function test_an_unsorted_car_shows_no_tabs(): void
    {
        $car = $this->car(['image_tags' => null, 'video_url' => null]);
        $this->get('/coche/' . $car->slug)->assertOk()->assertDontSee('class="car-tabs"', false);
    }
}
