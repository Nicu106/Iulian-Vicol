<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Support\CarPhotos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The car form's photographs (2026-10-16): one at a time to /admin/fotos, into
 * three groups, re-grouped and removed from the edit form, attached on save —
 * and the session answers that keep a long form on a phone from being lost.
 *
 * Every test runs on Storage::fake('public') and the in-memory database: no
 * file under storage/app/public and no row of the real site is touched.
 */
class CarPhotosTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'Tok3nTok3nTok3nTok3nTok3nTok3nTok3nTok3n';   // 40 alnum

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Queue::fake();   // WarmVehicleImages: not this test's business
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function base(array $more = []): array
    {
        return array_merge(['brand' => 'BMW', 'model' => '320d', 'year' => '2019', 'price' => '20000'], $more);
    }

    /** A car whose four gallery files and separate cover exist on the fake disk. */
    private function car(array $attrs = []): Vehicle
    {
        $slug = 'bmw-320d-2019-abcde';
        $paths = [];
        foreach (['a', 'b', 'c', 'd'] as $n) {
            Storage::disk('public')->put("vehicles/$slug/$n.jpg", 'x');
            $paths[] = "/storage/vehicles/$slug/$n.jpg";
        }
        Storage::disk('public')->put("vehicles/$slug/cover.jpg", 'x');

        return Vehicle::create(array_merge([
            'slug' => $slug, 'brand' => 'BMW', 'model' => '320d', 'title' => 'BMW 320d 2019',
            'year' => 2019, 'price' => 20000, 'status' => 'available',
            'cover_image' => "/storage/vehicles/$slug/cover.jpg",
            'gallery_images' => $paths,
            'image_tags' => [$paths[0] => 'exterior', "/storage/vehicles/$slug/cover.jpg" => 'exterior'],
        ], $attrs));
    }

    private function stage(User $u, string $tag = 'exterior', ?UploadedFile $f = null, string $token = self::TOKEN): string
    {
        $r = $this->actingAs($u)->post('/admin/fotos', [
            'photo' => $f ?? UploadedFile::fake()->image('IMG_0001.jpg', 1600, 1200),
            'token' => $token, 'tag' => $tag,
        ], ['Accept' => 'application/json']);
        $r->assertCreated();

        return $r->json('path');
    }

    // ---- the endpoint ------------------------------------------------------

    public function test_a_guest_cannot_upload_or_ping(): void
    {
        $this->postJson('/admin/fotos', ['token' => self::TOKEN])->assertStatus(401);
        $this->getJson('/admin/ping')->assertStatus(401);
    }

    public function test_one_photo_is_kept_under_its_token_and_answered_with_its_path(): void
    {
        $u = $this->admin();
        $path = $this->stage($u, 'interior');

        $this->assertMatchesRegularExpression('~^/storage/vehicles/_drafts/' . self::TOKEN . '/[A-Za-z0-9]{40}\.jpg$~', $path);
        Storage::disk('public')->assertExists(substr($path, 9));
    }

    public function test_png_and_webp_keep_their_type(): void
    {
        $u = $this->admin();
        $this->assertStringEndsWith('.png', $this->stage($u, 'exterior', UploadedFile::fake()->image('x.png', 800, 600)));
        if (function_exists('imagewebp')) {
            $this->assertStringEndsWith('.webp', $this->stage($u, 'exterior', UploadedFile::fake()->image('x.webp', 800, 600)));
        }
    }

    public function test_a_file_that_is_not_a_photo_is_refused_in_spanish(): void
    {
        $u = $this->admin();
        $r = $this->actingAs($u)->postJson('/admin/fotos', [
            'photo' => UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf'),
            'token' => self::TOKEN,
        ]);
        $r->assertStatus(422);
        $this->assertStringContainsString('no es una foto', $r->json('message'));
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_a_bad_token_is_refused(): void
    {
        $u = $this->admin();
        $this->actingAs($u)->postJson('/admin/fotos', [
            'photo' => UploadedFile::fake()->image('a.jpg'), 'token' => '../../etc',
        ])->assertStatus(422)->assertJsonValidationErrors('token');
    }

    public function test_an_iphone_heic_is_converted_to_jpeg(): void
    {
        if (!is_executable('/usr/bin/heif-enc') || !is_executable('/usr/bin/heif-convert')) {
            $this->markTestSkipped('libheif tools not installed');
        }
        $jpg = tempnam(sys_get_temp_dir(), 'h') . '.jpg';
        $heic = substr($jpg, 0, -4) . '.heic';
        $im = imagecreatetruecolor(640, 480);
        imagefill($im, 0, 0, imagecolorallocate($im, 20, 80, 200));
        imagejpeg($im, $jpg, 90);
        // This box has no HEVC ENCODER (it has the libde265 decoder, which is what
        // matters), so the fixture is the same HEIF container with AV1 inside.
        // A real iPhone HEIC was converted by hand once: 1440x960 → JPEG.
        exec('/usr/bin/heif-enc -A -q 60 ' . escapeshellarg($jpg) . ' -o ' . escapeshellarg($heic) . ' 2>&1', $o, $code);
        $this->assertSame(0, $code, implode("\n", $o));

        $u = $this->admin();
        $path = $this->stage($u, 'exterior', new UploadedFile($heic, 'IMG_4512.HEIC', 'image/heic', null, true));
        $this->assertStringEndsWith('.jpg', $path);
        $info = getimagesizefromstring(Storage::disk('public')->get(substr($path, 9)));
        $this->assertSame([640, 480, 'image/jpeg'], [$info[0], $info[1], $info['mime']]);
        @unlink($jpg); @unlink($heic);
    }

    public function test_a_staged_photo_can_be_withdrawn_only_with_its_own_token(): void
    {
        $u = $this->admin();
        $path = $this->stage($u);
        $other = 'Xx' . substr(self::TOKEN, 2);

        $this->actingAs($u)->deleteJson('/admin/fotos', ['token' => $other, 'path' => $path])->assertJson(['removed' => false]);
        Storage::disk('public')->assertExists(substr($path, 9));
        $this->actingAs($u)->deleteJson('/admin/fotos', ['token' => self::TOKEN, 'path' => $path])->assertJson(['removed' => true]);
        Storage::disk('public')->assertMissing(substr($path, 9));
    }

    public function test_ping_keeps_the_session_and_hands_back_the_token(): void
    {
        $r = $this->actingAs($this->admin())->getJson('/admin/ping');
        $r->assertOk()->assertJson(['ok' => true]);
        $this->assertNotEmpty($r->json('csrf'));
        $this->assertStringContainsString('no-store', $r->headers->get('Cache-Control'));
    }

    // ---- adding a car --------------------------------------------------------

    public function test_a_new_car_takes_its_staged_photos_in_three_groups(): void
    {
        $u = $this->admin();
        // chosen in a mixed order: interior first, a flaw, then two exteriors
        $in = $this->stage($u, 'interior');
        $fl = $this->stage($u, 'flaw');
        $e1 = $this->stage($u, 'exterior');
        $e2 = $this->stage($u, 'exterior');

        $r = $this->actingAs($u)->post('/admin/vehicles', $this->base([
            'photo_token' => self::TOKEN,
            'staged' => [
                ['path' => $in, 'tag' => 'interior'],
                ['path' => $fl, 'tag' => 'flaw'],
                ['path' => $e1, 'tag' => 'exterior'],
                ['path' => $e2, 'tag' => 'none'],      // re-grouped before saving
            ],
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ?si=abc',
        ]));
        $r->assertRedirect(route('admin.vehicles.index'))->assertSessionHas('draft_clear', 'mc-car:new');

        $car = Vehicle::firstOrFail();
        $g = $car->gallery_images;
        $dir = '/storage/vehicles/' . $car->slug . '/';
        $this->assertSame([$dir . basename($e1), $dir . basename($in), $dir . basename($fl), $dir . basename($e2)], $g,
            'exterior, interior, flaws, then unsorted — each in the order chosen');
        $this->assertSame([$g[0] => 'exterior', $g[1] => 'interior', $g[2] => 'flaw'], $car->image_tags);
        $this->assertSame($g[0], $car->cover_image, 'the cover is the first exterior photo');
        foreach ($g as $p) {
            Storage::disk('public')->assertExists(substr($p, 9));
        }
        $this->assertSame([], Storage::disk('public')->allFiles(CarPhotos::DRAFTS . '/' . self::TOKEN), 'nothing left in the draft');
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $car->video_url);
    }

    public function test_without_javascript_the_three_inputs_still_post_as_multipart(): void
    {
        $u = $this->admin();
        $this->actingAs($u)->post('/admin/vehicles', $this->base([
            'photos_interior' => [UploadedFile::fake()->image('i.jpg')],
            'photos_exterior' => [UploadedFile::fake()->image('e1.jpg'), UploadedFile::fake()->image('e2.jpg')],
            'photos_flaw'     => [UploadedFile::fake()->image('f.jpg')],
            'gallery_images'  => [UploadedFile::fake()->image('old-style.jpg')],
        ]))->assertRedirect();

        $car = Vehicle::firstOrFail();
        $this->assertCount(5, $car->gallery_images);
        $this->assertSame(['exterior', 'exterior', 'interior', 'flaw'], array_values($car->image_tags));
        $this->assertArrayNotHasKey($car->gallery_images[4], $car->image_tags, 'the general upload stays unsorted');
        $this->assertSame($car->gallery_images[0], $car->cover_image);
    }

    public function test_a_chosen_staged_cover_wins(): void
    {
        $u = $this->admin();
        $e = $this->stage($u, 'exterior');
        $i = $this->stage($u, 'interior');
        $this->actingAs($u)->post('/admin/vehicles', $this->base([
            'photo_token' => self::TOKEN, 'cover_from' => $i,
            'staged' => [['path' => $e, 'tag' => 'exterior'], ['path' => $i, 'tag' => 'interior']],
        ]))->assertRedirect();
        $car = Vehicle::firstOrFail();
        $this->assertSame('/storage/vehicles/' . $car->slug . '/' . basename($i), $car->cover_image);
    }

    public function test_a_staged_path_from_another_token_or_outside_the_drafts_is_ignored(): void
    {
        $u = $this->admin();
        $other = 'Zz' . substr(self::TOKEN, 2);
        $theirs = $this->stage($u, 'exterior', null, $other);
        Storage::disk('public')->put('vehicles/someone-else/secret.jpg', 'x');

        $this->actingAs($u)->post('/admin/vehicles', $this->base([
            'photo_token' => self::TOKEN,
            'staged' => [
                ['path' => $theirs, 'tag' => 'exterior'],
                ['path' => '/storage/vehicles/someone-else/secret.jpg', 'tag' => 'exterior'],
                ['path' => '/storage/vehicles/_drafts/' . self::TOKEN . '/../../someone-else/secret.jpg', 'tag' => 'exterior'],
            ],
        ]))->assertRedirect();

        $this->assertSame([], Vehicle::firstOrFail()->gallery_images);
        Storage::disk('public')->assertExists(substr($theirs, 9));
        Storage::disk('public')->assertExists('vehicles/someone-else/secret.jpg');
    }

    public function test_a_validation_error_keeps_the_staged_photos_in_the_form(): void
    {
        $u = $this->admin();
        $p = $this->stage($u, 'flaw');
        $r = $this->actingAs($u)->from('/admin/vehicles/create')->post('/admin/vehicles', [
            'brand' => '', 'model' => '', 'year' => '', 'price' => '',
            'photo_token' => self::TOKEN, 'staged' => [['path' => $p, 'tag' => 'flaw']],
        ]);
        $r->assertRedirect('/admin/vehicles/create')
          ->assertSessionHasErrors(['brand' => 'Escribe la marca.', 'model' => 'Escribe el modelo.', 'year' => 'Pon el año.', 'price' => 'Pon el precio.'])
          ->assertSessionHasInput('photo_token', self::TOKEN);
        Storage::disk('public')->assertExists(substr($p, 9));

        // and the form, drawn again, still shows it in Desperfectos
        $html = $this->actingAs($u)->get('/admin/vehicles/create')->assertOk()->getContent();
        $this->assertStringContainsString('value="' . $p . '"', $html);
        $this->assertStringContainsString('value="' . self::TOKEN . '"', $html);
    }

    public function test_numbers_written_the_spanish_way_are_accepted(): void
    {
        $this->actingAs($this->admin())->post('/admin/vehicles', $this->base([
            'price' => '23.700 €', 'mileage' => '137.000 km', 'original_price' => '25.900',
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $car = Vehicle::firstOrFail();
        $this->assertSame([23700.0, 137000, 25900.0], [$car->price, $car->mileage, $car->original_price]);
    }

    public function test_every_youtube_link_form_is_accepted_and_stored_canonical(): void
    {
        $u = $this->admin();
        $car = $this->car();
        $cases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ'            => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ?si=Zx9_abc'                => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/9bNWVtrBCy4?is=dEAdFZYObdXEdMAM'       => 'https://www.youtube.com/watch?v=9bNWVtrBCy4',
            'https://m.youtube.com/watch?v=dQw4w9WgXcQ&t=1m30s'      => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=90s',
            'https://www.youtube.com/shorts/aBcDeFgHiJk'             => 'https://www.youtube.com/watch?v=aBcDeFgHiJk',
            'https://www.youtube.com/embed/dQw4w9WgXcQ?start=42'     => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=42s',
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'     => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://www.youtube.com/live/dQw4w9WgXcQ?feature=share' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtu.be/dQw4w9WgXcQ?t=75'                              => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=75s',
            'https://www.youtube.com/watch?feature=shared&v=dQw4w9WgXcQ#t=10' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10s',
        ];
        foreach ($cases as $in => $out) {
            $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, $this->base(['video_url' => $in]))
                ->assertSessionHasNoErrors();
            $this->assertSame($out, $car->fresh()->video_url, $in);
        }
        // and emptied
        $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, $this->base(['video_url' => '']))->assertSessionHasNoErrors();
        $this->assertNull($car->fresh()->video_url);
    }

    public function test_links_that_are_not_one_youtube_video_are_refused(): void
    {
        $u = $this->admin();
        $car = $this->car(['video_url' => 'https://youtu.be/dQw4w9WgXcQ']);
        foreach ([
            'https://vimeo.com/123456789',
            'https://www.youtube.com/@ivmotorclass',
            'https://www.youtube.com/playlist?list=PL123',
            'https://youtu.be/short',
            'https://evil.example/watch?v=dQw4w9WgXcQ',
            'javascript:alert(1)//youtu.be/dQw4w9WgXcQ',
            'no es un enlace',
        ] as $bad) {
            $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, $this->base(['video_url' => $bad]))
                ->assertSessionHasErrors(['video_url' => 'Este enlace no es de un vídeo de YouTube. Cópialo con el botón «Compartir» del vídeo.']);
        }
        $this->assertSame('https://youtu.be/dQw4w9WgXcQ', $car->fresh()->video_url, 'a refused link changes nothing');
    }

    // ---- editing a car -------------------------------------------------------

    public function test_photos_are_regrouped_removed_and_added_in_one_save(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$a, $b, $c, $d] = $car->gallery_images;
        $cover = $car->cover_image;
        $new = $this->stage($u, 'flaw');

        $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, $this->base([
            'photos' => [
                ['path' => $cover, 'tag' => 'exterior'],
                ['path' => $a, 'tag' => 'none'],                 // exterior → sin clasificar
                ['path' => $b, 'tag' => 'interior'],             // sin clasificar → interior
                ['path' => $c, 'tag' => 'flaw', 'remove' => '1'],// goes, and its tag with it
                ['path' => $d, 'tag' => 'interior'],
                ['path' => '/storage/vehicles/other/x.jpg', 'tag' => 'exterior', 'remove' => '1'],   // not this car's
            ],
            'photo_token' => self::TOKEN,
            'staged' => [['path' => $new, 'tag' => 'flaw']],
        ]))->assertSessionHasNoErrors()->assertSessionHas('draft_clear', 'mc-car:' . $car->slug);

        $car->refresh();
        $n = '/storage/vehicles/' . $car->slug . '/' . basename($new);
        $this->assertSame([$a, $b, $d, $n], $car->gallery_images, 'order kept, the new one appended');
        $this->assertSame([$cover => 'exterior', $b => 'interior', $d => 'interior', $n => 'flaw'], $car->image_tags);
        Storage::disk('public')->assertMissing(substr($c, 9));
        Storage::disk('public')->assertExists(substr($n, 9));
        $this->assertSame($cover, $car->cover_image);
    }

    public function test_removing_the_cover_falls_back_to_the_first_exterior_photo(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$a, $b, $c] = $car->gallery_images;
        $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, $this->base([
            'photos' => [
                ['path' => $car->cover_image, 'tag' => 'exterior', 'remove' => '1'],
                ['path' => $a, 'tag' => 'interior'],
                ['path' => $c, 'tag' => 'exterior'],
            ],
        ]))->assertSessionHasNoErrors();
        $car->refresh();
        $this->assertSame($c, $car->cover_image);
        Storage::disk('public')->assertMissing('vehicles/' . $car->slug . '/cover.jpg');
        $this->assertArrayNotHasKey('/storage/vehicles/' . $car->slug . '/cover.jpg', $car->image_tags);
    }

    public function test_cover_from_must_already_be_on_the_car(): void
    {
        $u = $this->admin();
        $car = $this->car();
        $cover = $car->cover_image;
        Storage::disk('public')->put('vehicles/other/x.jpg', 'x');
        $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, $this->base(['cover_from' => '/storage/vehicles/other/x.jpg']));
        $this->assertSame($cover, $car->fresh()->cover_image);
        $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, $this->base(['cover_from' => $car->gallery_images[2]]));
        $this->assertSame($car->gallery_images[2], $car->fresh()->cover_image);
    }

    public function test_the_older_form_fields_still_work(): void
    {
        $u = $this->admin();
        $car = $this->car();
        [$a, $b] = $car->gallery_images;
        Storage::disk('public')->put('vehicles/other/keep.jpg', 'x');
        $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, $this->base([
            'removed_gallery_images' => json_encode([$a, '/storage/vehicles/other/keep.jpg']),
            'gallery_images' => [UploadedFile::fake()->image('more.jpg')],
        ]))->assertSessionHasNoErrors();
        $car->refresh();
        $this->assertNotContains($a, $car->gallery_images);
        $this->assertArrayNotHasKey($a, $car->image_tags ?? []);
        $this->assertCount(4, $car->gallery_images);
        Storage::disk('public')->assertMissing(substr($a, 9));
        Storage::disk('public')->assertExists('vehicles/other/keep.jpg');
    }

    public function test_saving_without_touching_the_photos_changes_nothing(): void
    {
        $u = $this->admin();
        $car = $this->car();
        $before = [$car->gallery_images, $car->cover_image, $car->image_tags];
        $this->actingAs($u)->post('/admin/vehicles/' . $car->slug, $this->base(['price' => '19500']))->assertSessionHasNoErrors();
        $car->refresh();
        $this->assertSame($before, [$car->gallery_images, $car->cover_image, $car->image_tags]);
        $this->assertSame(19500.0, $car->price);
    }

    // ---- the session --------------------------------------------------------

    private function csrfRoute(): void
    {
        // CSRF is not checked under PHPUnit, so the failure is thrown by hand on
        // an admin path, exactly as ValidateCsrfToken throws it.
        Route::middleware('web')->post('/admin/_csrf-probe', fn () => throw new TokenMismatchException('CSRF token mismatch.'));
    }

    public function test_an_expired_session_goes_to_sign_in_and_comes_back_to_the_form(): void
    {
        $this->csrfRoute();
        $form = url('/admin/vehicles/bmw-320d-2019-abcde/edit');
        $r = $this->post('/admin/_csrf-probe', ['brand' => 'BMW'], ['referer' => $form]);
        $r->assertRedirect(route('login'))->assertSessionHas('url.intended', $form);
        $this->assertStringContainsString('Tu sesión había caducado', session('status'));

        $this->get(route('login'))->assertOk()->assertSee('Tu sesión había caducado');
    }

    public function test_a_stale_token_while_signed_in_returns_to_the_form_with_what_was_typed(): void
    {
        $this->csrfRoute();
        $form = url('/admin/vehicles/create');
        $this->actingAs($this->admin())->post('/admin/_csrf-probe', ['brand' => 'Audi', 'description' => 'Muy cuidado'], ['referer' => $form])
            ->assertRedirect($form)
            ->assertSessionHasInput('brand', 'Audi')
            ->assertSessionHas('error');
    }

    public function test_the_json_answer_carries_a_fresh_token(): void
    {
        $this->csrfRoute();
        $r = $this->actingAs($this->admin())->postJson('/admin/_csrf-probe', []);
        $r->assertStatus(419)->assertJson(['auth' => true]);
        $this->assertNotEmpty($r->json('csrf'));
    }

    public function test_a_public_page_keeps_the_standard_419(): void
    {
        Route::middleware('web')->post('/_csrf-public', fn () => throw new TokenMismatchException('CSRF token mismatch.'));
        $this->post('/_csrf-public')->assertStatus(419);
    }
}
