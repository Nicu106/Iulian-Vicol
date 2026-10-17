<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Support\CarPhotos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The order of a car's photographs (2026-10-17): set in the form by drag and
 * drop, posted as photo_order, kept in gallery_images, and followed by every
 * tab of the car page — the cover still the cover.
 *
 * Storage::fake('public') and the in-memory database: no file and no row of
 * the real site is touched.
 */
class CarPhotoOrderTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'Ord3rTok3nOrd3rTok3nOrd3rTok3nOrd3rTok3n';   // 40 alnum
    private const SLUG = 'audi-a4-avant-2018-order';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Queue::fake();
        Http::fake(['i.ytimg.com/*' => Http::response('', 200)]);
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function p(string $n): string
    {
        return '/storage/vehicles/' . self::SLUG . '/' . $n . '.jpg';
    }

    /**
     * The shape every car from the old site has: the cover a separate file
     * outside the gallery. Exterior a b, interior c d, a flaw e, one unsorted f.
     */
    private function car(array $attrs = []): Vehicle
    {
        foreach (['cover', 'a', 'b', 'c', 'd', 'e', 'f'] as $n) {
            Storage::disk('public')->put('vehicles/' . self::SLUG . "/$n.jpg", 'x');
        }

        return Vehicle::create(array_merge([
            'slug' => self::SLUG, 'brand' => 'Audi', 'model' => 'A4 Avant', 'title' => 'Audi A4 Avant 2018',
            'year' => 2018, 'price' => 21900, 'status' => 'available',
            'cover_image' => $this->p('cover'),
            'gallery_images' => [$this->p('a'), $this->p('b'), $this->p('c'), $this->p('d'), $this->p('e'), $this->p('f')],
            'image_tags' => [
                $this->p('cover') => 'exterior', $this->p('a') => 'exterior', $this->p('b') => 'exterior',
                $this->p('c') => 'interior', $this->p('d') => 'interior', $this->p('e') => 'flaw',
            ],
        ], $attrs));
    }

    /** What the form posts for the photos already on the car: each with its group. */
    private function rows(Vehicle $car, array $tags = [], array $remove = []): array
    {
        $out = [];
        foreach (CarPhotos::listFor($car->toArray()) as $ph) {
            $row = ['path' => $ph['path'], 'tag' => $tags[$ph['path']] ?? ($ph['tag'] ?? 'none')];
            if (in_array($ph['path'], $remove, true)) {
                $row['remove'] = '1';
            }
            $out[] = $row;
        }

        return $out;
    }

    private function save(User $u, Vehicle $car, array $more)
    {
        return $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, array_merge(
            ['brand' => 'Audi', 'model' => 'A4 Avant', 'year' => '2018', 'price' => '21900'], $more
        ))->assertSessionHasNoErrors();
    }

    private function stage(User $u, string $tag): string
    {
        return $this->actingAs($u)->post('/admin/fotos', [
            'photo' => UploadedFile::fake()->image('IMG.jpg', 800, 600), 'token' => self::TOKEN, 'tag' => $tag,
        ], ['Accept' => 'application/json'])->assertCreated()->json('path');
    }

    // ---- the server keeps the order ---------------------------------------------

    public function test_photos_keep_the_order_set_within_their_groups(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);

        $this->save($u, $car, ['photos' => $this->rows($car), 'cover_from' => $cv,
            'photo_order' => json_encode([$b, $cv, $a, $d, $c, $e, $f])]);

        $car->refresh();
        $this->assertSame([$b, $cv, $a, $d, $c, $e, $f], $car->gallery_images,
            'the order he set, the old site\'s separate cover now in its place among the others');
        $this->assertSame($cv, $car->cover_image, 'the cover stays the cover');
        foreach ([$cv, $a, $b, $c, $d, $e, $f] as $p) {
            Storage::disk('public')->assertExists(substr($p, 9));
        }
    }

    public function test_a_photo_dragged_into_another_group_takes_that_group_and_that_place(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);

        // c (interior) dropped between a and the cover; f (unsorted) dropped first in Desperfectos
        $this->save($u, $car, [
            'photos' => $this->rows($car, [$c => 'exterior', $f => 'flaw']),
            'photo_order' => json_encode([$a, $c, $cv, $b, $d, $f, $e]),
        ]);

        $car->refresh();
        $this->assertSame([$a, $c, $cv, $b, $d, $f, $e], $car->gallery_images);
        $this->assertSame('exterior', $car->image_tags[$c]);
        $this->assertSame('flaw', $car->image_tags[$f]);
        $this->assertSame($cv, $car->cover_image);
    }

    public function test_groups_come_out_in_the_pages_order_whatever_order_the_list_arrives_in(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);

        // the form's own top-to-bottom order puts "Sin clasificar" first, and a list
        // could arrive mixed: the gallery is still Exterior, Interior, Desperfectos, unsorted
        $this->save($u, $car, ['photos' => $this->rows($car), 'photo_order' => json_encode([$f, $e, $d, $b, $c, $a, $cv])]);

        $this->assertSame([$b, $a, $cv, $d, $c, $e, $f], $car->fresh()->gallery_images);
    }

    public function test_new_photos_take_the_place_they_were_dropped_in_among_the_old(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);
        $n1 = $this->stage($u, 'exterior');
        $n2 = $this->stage($u, 'interior');

        $this->save($u, $car, [
            'photos' => $this->rows($car),
            'photo_token' => self::TOKEN,
            'staged' => [['path' => $n1, 'tag' => 'exterior'], ['path' => $n2, 'tag' => 'interior']],
            'photo_order' => json_encode([$n1, $cv, $a, $b, $c, $n2, $d, $e, $f]),
        ]);

        $car->refresh();
        $m1 = '/storage/vehicles/' . self::SLUG . '/' . basename($n1);
        $m2 = '/storage/vehicles/' . self::SLUG . '/' . basename($n2);
        $this->assertSame([$m1, $cv, $a, $b, $c, $m2, $d, $e, $f], $car->gallery_images, 'the staged paths are followed to where they were moved');
        $this->assertSame(['exterior', 'interior'], [$car->image_tags[$m1], $car->image_tags[$m2]]);
        $this->assertSame($cv, $car->cover_image, 'a new photo first in Exterior does not take the chosen cover');
        Storage::disk('public')->assertExists(substr($m1, 9));
    }

    public function test_a_photo_removed_in_the_same_save_goes_and_the_rest_keep_the_order(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);

        $this->save($u, $car, [
            'photos' => $this->rows($car, [], [$b]),
            'photo_order' => json_encode([$b, $a, $cv, $d, $c, $e, $f]),   // b dragged first, then marked ×
        ]);

        $car->refresh();
        $this->assertSame([$a, $cv, $d, $c, $e, $f], $car->gallery_images);
        $this->assertArrayNotHasKey($b, $car->image_tags);
        Storage::disk('public')->assertMissing(substr($b, 9));
    }

    public function test_removing_the_cover_after_reordering_falls_back_to_the_first_exterior_in_the_new_order(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);

        $this->save($u, $car, [
            'photos' => $this->rows($car, [], [$cv]),
            'photo_order' => json_encode([$b, $a, $cv, $c, $d, $e, $f]),
        ]);

        $car->refresh();
        $this->assertSame($b, $car->cover_image, 'the one he put first, not the one uploaded first');
        $this->assertNotContains($cv, $car->gallery_images);
        Storage::disk('public')->assertMissing(substr($cv, 9));
    }

    public function test_choosing_another_cover_keeps_the_old_one_among_the_photos(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);

        $this->save($u, $car, ['photos' => $this->rows($car), 'cover_from' => $b,
            'photo_order' => json_encode([$cv, $a, $b, $c, $d, $e, $f])]);

        $car->refresh();
        $this->assertSame($b, $car->cover_image);
        $this->assertContains($cv, $car->gallery_images, 'the old separate cover is not dropped from the car any more');
        Storage::disk('public')->assertExists(substr($cv, 9));
    }

    public function test_paths_that_are_not_this_cars_and_garbage_are_ignored(): void
    {
        $u = $this->admin();
        $car = $this->car();
        Storage::disk('public')->put('vehicles/other/x.jpg', 'x');
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);

        $this->save($u, $car, ['photos' => $this->rows($car),
            'photo_order' => json_encode(['/storage/vehicles/other/x.jpg', $b, 42, ['x'], null, $a, '../../.env'])]);
        $g = $car->fresh()->gallery_images;
        $this->assertNotContains('/storage/vehicles/other/x.jpg', $g);
        $this->assertSame([$b, $a, $c, $d, $e, $f], $g, 'the named ones first in their group, the rest after, as they were');
        $this->assertSame($cv, $car->fresh()->cover_image, 'a cover the list does not name stays outside the gallery');

        foreach (['not json', '{"a":1}', '', '[]'] as $junk) {
            $was = $car->fresh()->gallery_images;
            $this->save($u, $car, ['photos' => $this->rows($car->fresh()), 'photo_order' => $junk]);
            $this->assertSame($was, $car->fresh()->gallery_images, var_export($junk, true));
        }
    }

    public function test_without_an_order_the_gallery_is_left_as_it_was(): void
    {
        $u = $this->admin();
        $car = $this->car();
        $before = [$car->gallery_images, $car->cover_image];
        $this->save($u, $car, ['photos' => $this->rows($car)]);   // the form without JavaScript
        $car->refresh();
        $this->assertSame($before, [$car->gallery_images, $car->cover_image]);
    }

    public function test_a_new_car_keeps_the_order_and_its_cover_is_the_first_exterior_in_it(): void
    {
        $u = $this->admin();
        $e1 = $this->stage($u, 'exterior');
        $e2 = $this->stage($u, 'exterior');
        $i1 = $this->stage($u, 'interior');
        $this->actingAs($u)->post('/admin/vehicles', [
            'brand' => 'BMW', 'model' => '320d', 'year' => '2019', 'price' => '20000',
            'photo_token' => self::TOKEN,
            'staged' => [['path' => $e1, 'tag' => 'exterior'], ['path' => $e2, 'tag' => 'exterior'], ['path' => $i1, 'tag' => 'interior']],
            'photo_order' => json_encode([$e2, $e1, $i1]),
        ])->assertSessionHasNoErrors();

        $car = Vehicle::firstOrFail();
        $dir = '/storage/vehicles/' . $car->slug . '/';
        $this->assertSame([$dir . basename($e2), $dir . basename($e1), $dir . basename($i1)], $car->gallery_images);
        $this->assertSame($dir . basename($e2), $car->cover_image, 'no cover chosen: the first exterior in his order');
    }

    // ---- what the form shows ----------------------------------------------------

    public function test_the_edit_form_shows_the_saved_order_with_the_cover_where_he_put_it(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b] = array_map([$this, 'p'], ['cover', 'a', 'b']);
        $this->save($u, $car, ['photos' => $this->rows($car), 'photo_order' => json_encode([$a, $b, $cv])]);

        $html = $this->actingAs($u)->get('/admin/vehicles/' . self::SLUG . '/edit')->assertOk()->getContent();
        $at = fn ($p) => strpos($html, 'data-path="' . $p . '"');
        $this->assertTrue($at($a) < $at($b) && $at($b) < $at($cv), 'Exterior reads a, b, then the cover');
        $this->assertMatchesRegularExpression('~name="cover_from" value="' . preg_quote($cv, '~') . '" checked~', $html);
        $this->assertStringContainsString('name="photo_order" id="ad-order"', $html);
        $this->assertSame(7, substr_count(strstr($html, '<script', true), 'class="ad-shot__mv"'), 'one keyboard handle per photo');
        $this->assertStringContainsString('id="ad-dnd-live" aria-live="assertive"', $html);
    }

    public function test_a_failed_save_comes_back_in_the_order_he_left_the_photos_in(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b] = array_map([$this, 'p'], ['cover', 'a', 'b']);
        $new = $this->stage($u, 'exterior');

        $this->actingAs($u)->from('/admin/vehicles/' . self::SLUG . '/edit')->post('/admin/vehicles/' . self::SLUG, [
            'brand' => '', 'model' => 'A4 Avant', 'year' => '2018', 'price' => '21900',
            'photos' => $this->rows($car), 'photo_token' => self::TOKEN,
            'staged' => [['path' => $new, 'tag' => 'exterior']],
            'photo_order' => json_encode([$b, $new, $cv, $a]),
        ])->assertSessionHasErrors('brand');

        $html = $this->actingAs($u)->get('/admin/vehicles/' . self::SLUG . '/edit')->getContent();
        $at = fn ($p) => strpos($html, 'data-path="' . $p . '"');
        $this->assertTrue($at($b) < $at($new) && $at($new) < $at($cv) && $at($cv) < $at($a),
            'b, the new one, the cover, a — the staged photo between two of the car\'s');
        $this->assertSame([$this->p('a'), $this->p('b'), $this->p('c'), $this->p('d'), $this->p('e'), $this->p('f')], $car->fresh()->gallery_images, 'nothing saved');
    }

    public function test_the_copy_on_the_phone_is_saved_with_its_order_after_the_session_came_back(): void
    {
        // The phone kept the form (fields, the photos sent under its token, the
        // order); the session expired; he signed in again in another tab, came
        // back, the copy was restored and he pressed Guardar. What arrives is an
        // ordinary post with the old token: it must land in that order.
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);
        $n = $this->stage($u, 'flaw');
        auth()->logout();
        $this->post('/admin/vehicles/' . self::SLUG, ['brand' => 'Audi'])->assertRedirect(route('login'));
        $this->assertSame([$a, $b, $c, $d, $e, $f], $car->fresh()->gallery_images, 'nothing changed while signed out');

        $this->save($this->admin(), $car, [
            'photos' => $this->rows($car, [$d => 'flaw']), 'photo_token' => self::TOKEN,
            'staged' => [['path' => $n, 'tag' => 'flaw']],
            'photo_order' => json_encode([$cv, $a, $b, $c, $n, $d, $e, $f]),
        ]);
        $m = '/storage/vehicles/' . self::SLUG . '/' . basename($n);
        $this->assertSame([$cv, $a, $b, $c, $m, $d, $e, $f], $car->fresh()->gallery_images);
    }

    // ---- what the car page does with it -----------------------------------------

    public function test_every_tab_of_the_car_page_follows_the_saved_order_and_todas_opens_on_the_cover(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$cv, $a, $b, $c, $d, $e, $f] = array_map([$this, 'p'], ['cover', 'a', 'b', 'c', 'd', 'e', 'f']);
        // the cover third in Exterior; interior reversed; the unsorted one at the end
        $order = [$a, $b, $cv, $d, $c, $e, $f];
        $this->save($u, $car, ['photos' => $this->rows($car), 'photo_order' => json_encode($order)]);

        $html = $this->get('/coche/' . self::SLUG)->assertOk()->getContent();
        preg_match_all('~<a class="car-thumb[^>]*?href="([^"]+)"[^>]*?data-group="([a-z]+)"\s+data-n="(\d+)"\s+data-o="(-?\d+)"~s', $html, $m, PREG_SET_ORDER);
        $this->assertCount(7, $m, 'seven photographs, the cover once');
        $paths = array_map(function ($x) {
            parse_str((string) parse_url(html_entity_decode($x[1]), PHP_URL_QUERY), $q);
            return ($q['p'] ?? null) ?: html_entity_decode($x[1]);
        }, $m);
        $byPath = array_combine(array_map(fn ($p) => basename(parse_url($p, PHP_URL_PATH) ?: $p), $paths), $m);

        // "Todas" (the document order): the cover first — it is the photo the buyer
        // tapped in the catalogue — then his order
        $todas = array_keys($byPath);
        $this->assertSame(['cover.jpg', 'a.jpg', 'b.jpg', 'd.jpg', 'c.jpg', 'e.jpg', 'f.jpg'], $todas);
        $this->assertSame(range(1, 7), array_map(fn ($x) => (int) $x[3], array_values($byPath)), 'data-n is the Todas position');

        // each group's tab sorts by data-o (the script) and must read exactly his order
        foreach (['exterior' => ['a.jpg', 'b.jpg', 'cover.jpg'], 'interior' => ['d.jpg', 'c.jpg'], 'flaw' => ['e.jpg']] as $g => $want) {
            $in = array_filter($byPath, fn ($x) => $x[2] === $g);
            uasort($in, fn ($x, $y) => (int) $x[4] <=> (int) $y[4]);
            $this->assertSame($want, array_keys($in), $g);
        }
        $this->assertMatchesRegularExpression('~data-group="all">Todas <span>7</span>~', $html);
        $this->assertMatchesRegularExpression('~data-group="exterior">Exterior <span>3</span>~', $html);
        // the stage opens on the cover
        $this->assertMatchesRegularExpression('~id="stage"[^>]*cover\.jpg|cover\.jpg[^>]*id="stage"~s', $html);
        // and the script that sorts a tab by data-o is on the page
        $this->assertStringContainsString("var key = g === 'all' ? 'data-n' : 'data-o';", $html);
    }

    public function test_a_car_never_reordered_shows_as_before(): void
    {
        $car = $this->car();
        $html = $this->get('/coche/' . self::SLUG)->assertOk()->getContent();
        preg_match_all('~data-n="(\d+)"\s+data-o="(-?\d+)"~', $html, $m);
        $this->assertSame(['1', '2', '3', '4', '5', '6', '7'], $m[1]);
        $this->assertSame(['-1', '0', '1', '2', '3', '4', '5'], $m[2], 'the separate cover first in every tab, then the gallery as it was');
    }

    // ---- the rule itself ----------------------------------------------------------

    public function test_order_sorts_by_group_then_position_and_never_adds_or_drops(): void
    {
        $g = ['/x/1', '/x/2', '/x/3', '/x/4', '/x/5'];
        $tags = ['/x/1' => 'interior', '/x/2' => 'exterior', '/x/4' => 'flaw', '/x/5' => 'exterior'];
        $this->assertSame(['/x/5', '/x/2', '/x/1', '/x/4', '/x/3'], CarPhotos::order($g, $tags, ['/x/3', '/x/5', '/x/1', '/x/2', '/x/4']));
        // one not named keeps its place after the named ones of its group; duplicates count once
        $this->assertSame(['/x/2', '/x/5', '/x/1', '/x/4', '/x/3'], CarPhotos::order($g, $tags, ['/x/2', '/x/2', '/x/9']));
        $this->assertNull(CarPhotos::orderOf('{"a":"b"}'));
        $this->assertNull(CarPhotos::orderOf(''));
        $this->assertNull(CarPhotos::orderOf('[]'), 'an empty list is no order: the gallery is left alone');
        $this->assertNull(CarPhotos::orderOf('[1, null]'));
        $this->assertSame(['/a', '/b'], CarPhotos::orderOf('["/a", 3, "", "/b", null]'));
        $this->assertSame(['/a'], CarPhotos::orderOf(['/a']));
    }
}
