<?php

namespace Tests\Feature;

use App\Models\ReviewLink;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * A customer writes their own opinion through a link (App\Models\ReviewLink,
 * Admin\ReviewLinkController, OpinionController), 2026-10-29.
 *
 * With MC_DUMP=/some/dir set, the panel's page with its window and the four
 * states of /opinion are written there to be photographed in a browser.
 */
class ReviewLinkTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function dump(string $name, string $html): void
    {
        if ($dir = getenv('MC_DUMP')) {
            file_put_contents($dir . '/' . $name . '.html', $html);
        }
    }

    /** A 400×300 JPEG that says "turn me 90° clockwise" and carries a GPS block. */
    private function phonePhoto(): UploadedFile
    {
        $im = imagecreatetruecolor(400, 300);
        imagefill($im, 0, 0, imagecolorallocate($im, 30, 90, 200));
        ob_start();
        imagejpeg($im, null, 90);
        $jpeg = ob_get_clean();

        // TIFF, big-endian: IFD0 = Orientation 6 + GPS pointer; GPS IFD = LatitudeRef "N"
        $ifd0 = pack('n', 2)
            . pack('nnNnn', 0x0112, 3, 1, 6, 0)
            . pack('nnNN', 0x8825, 4, 1, 8 + 2 + 24 + 4)
            . pack('N', 0);
        $gps = pack('n', 1) . pack('nnN', 0x0001, 2, 2) . "N\0\0\0" . pack('N', 0);
        $tiff = "MM\0*" . pack('N', 8) . $ifd0 . $gps;
        $app1 = "\xFF\xE1" . pack('n', strlen("Exif\0\0" . $tiff) + 2) . "Exif\0\0" . $tiff;
        $path = tempnam(sys_get_temp_dir(), 'op') . '.jpg';
        file_put_contents($path, substr($jpeg, 0, 2) . $app1 . substr($jpeg, 2));

        return new UploadedFile($path, 'IMG_0001.jpg', 'image/jpeg', null, true);
    }

    public function test_the_panel_makes_a_link_with_its_qr_and_whatsapp_message(): void
    {
        $r = $this->actingAs($this->admin())
            ->postJson(route('admin.review-links.store'), ['days' => 3, 'name' => '  María  '])
            ->assertOk()->json();

        $this->assertMatchesRegularExpression('#/opinion/[A-Za-z0-9]{20}$#', $r['url']);
        $this->assertStringContainsString('<svg', $r['qr']);
        $this->assertSame('María', $r['name']);
        $this->assertStringContainsString(rawurlencode($r['url']), $r['wa']);
        $this->assertStringStartsWith('https://wa.me/?text=Hola%20Mar%C3%ADa', $r['wa']);
        $link = ReviewLink::firstOrFail();
        $this->assertEqualsWithDelta(now()->addDays(3)->timestamp, $link->expires_at->timestamp, 5);

        // a period that is not on the list
        $this->actingAs($this->admin())->postJson(route('admin.review-links.store'), ['days' => 400])->assertStatus(422);
        // and nobody else can make one
        auth()->logout();
        $this->post(route('admin.review-links.store'), ['days' => 7])->assertRedirect();
        $this->assertSame(1, ReviewLink::count());
    }

    public function test_the_panel_lists_open_links_and_offers_the_window(): void
    {
        $open = ReviewLink::open(7, 'María');
        ReviewLink::open(1, null)->update(['expires_at' => now()->subMinute()]);   // expired: not listed
        $html = $this->actingAs($this->admin())->get(route('admin.testimonials.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-rv-add', $html);
        $this->assertStringContainsString('id="ad-rv"', $html);
        $this->assertStringContainsString('Un enlace abierto', $html);
        $this->assertStringContainsString('Abierto hasta el ' . $open->until(), $html);
        $this->assertSame(1, substr_count($html, 'data-rv-show='));
        $this->dump('admin-opiniones', $html);

        $this->actingAs($this->admin())->getJson(route('admin.review-links.show', $open))->assertOk()->assertJsonPath('name', 'María');
        $this->post(route('admin.review-links.close', $open))->assertRedirect(route('admin.testimonials.index'));
        $this->assertFalse($open->fresh()->isOpen());
        $this->getJson(route('admin.review-links.show', $open))->assertStatus(410);
    }

    public function test_a_customer_writes_through_the_link_once(): void
    {
        Storage::fake('public');
        $link = ReviewLink::open(7, 'María');

        $form = $this->get(route('opinion', $link->token))->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')->getContent();
        $this->assertStringContainsString('María, cuéntame qué&nbsp;tal', $form);
        $this->assertStringContainsString('value="María"', $form);
        $this->assertStringContainsString('name="robots" content="noindex, nofollow"', $form);
        $this->assertStringNotContainsString('application/ld+json', $form);
        $this->dump('opinion-form', $form);

        $this->post(route('opinion.store', $link->token), [
            'name' => 'María G.', 'quote' => 'Todo perfecto, el coche como en las fotos y me lo explicó todo.',
            'photo' => $this->phonePhoto(), 'consent' => '1',
        ])->assertRedirect(route('opinion', $link->token))->assertSessionHasNoErrors();

        $t = Testimonial::firstOrFail();
        $this->assertFalse($t->is_active);
        $this->assertSame('María G.', $t->author_name);
        $link->refresh();
        $this->assertNotNull($link->used_at);
        $this->assertSame($t->id, $link->testimonial_id);

        // the photo: upright (300 wide, 400 tall) and with nothing the phone wrote
        $file = Storage::disk('public')->path(str_replace('/storage/', '', $t->image_path));
        [$w, $h] = getimagesize($file);
        $this->assertSame([300, 400], [$w, $h]);
        $this->assertStringNotContainsString('Exif', file_get_contents($file));

        $thanks = $this->get(route('opinion', $link->token))->assertOk()->getContent();
        $this->assertStringContainsString('Gracias, María G.', $thanks);
        $this->dump('opinion-gracias', $thanks);

        // the same link again, from another browser: no second opinion
        $this->flushSession();
        $this->post(route('opinion.store', $link->token), ['name' => 'X', 'quote' => str_repeat('a', 30), 'consent' => '1'])
            ->assertRedirect(route('opinion', $link->token));
        $this->assertSame(1, Testimonial::count());
        $this->assertStringContainsString('ya me&nbsp;llegó', $this->get(route('opinion', $link->token))->getContent());

        // the panel marks it as new
        $this->actingAs($this->admin())->get(route('admin.testimonials.index'))->assertSee('Nueva · la escribió el cliente');
    }

    public function test_what_the_form_refuses(): void
    {
        Storage::fake('public');
        $link = ReviewLink::open(7, null);
        $go = fn ($data) => $this->from(route('opinion', $link->token))->post(route('opinion.store', $link->token), $data);

        $go(['name' => 'Ana', 'quote' => 'Muy bien todo, repetiría sin duda.'])->assertSessionHasErrors('consent');
        $go(['name' => 'Ana', 'quote' => 'Bien', 'consent' => '1'])->assertSessionHasErrors('quote');
        $go(['name' => '', 'quote' => 'Muy bien todo, repetiría sin duda.', 'consent' => '1'])->assertSessionHasErrors('name');
        $go(['name' => 'Ana', 'quote' => 'Muy bien todo, repetiría sin duda.', 'consent' => '1',
             'photo' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])->assertSessionHasErrors('photo');
        $this->assertSame(0, Testimonial::count());
        $this->assertTrue($link->fresh()->isOpen());

        // without a photo is fine
        $go(['name' => 'Ana', 'quote' => 'Muy bien todo, repetiría sin duda.', 'consent' => '1'])->assertSessionHasNoErrors();
        $this->assertNull(Testimonial::firstOrFail()->image_path);
    }

    public function test_a_closed_or_unknown_link(): void
    {
        $link = ReviewLink::open(1, 'Pepe');
        $link->update(['expires_at' => now()->subSecond()]);

        $html = $this->get(route('opinion', $link->token))->assertOk()->getContent();
        $this->assertStringContainsString('Este enlace ya está&nbsp;cerrado.', $html);
        $this->dump('opinion-cerrado', $html);
        $this->post(route('opinion.store', $link->token), ['name' => 'Pepe', 'quote' => str_repeat('b', 30), 'consent' => '1'])
            ->assertRedirect(route('opinion', $link->token));
        $this->assertSame(0, Testimonial::count());

        $this->get('/opinion/' . str_repeat('A', 20))->assertNotFound();
        $this->get('/opinion/short')->assertNotFound();
    }
}
