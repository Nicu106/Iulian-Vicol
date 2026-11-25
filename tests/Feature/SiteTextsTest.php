<?php

namespace Tests\Feature;

use App\Models\SiteText;
use App\Models\SiteTextEdit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /muestras/textos (SiteTextController), 2026-11-25: the client rewrites the
 * site's texts there, behind the /muestras PIN, and nothing he writes is lost.
 */
class SiteTextsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Storage::fake('local');   // never the real textos/cambios.jsonl
    }

    private function in(): static
    {
        return $this->withSession(['muestras_ok' => true]);
    }

    public function test_the_texts_load_from_the_data_file_and_keep_what_was_written(): void
    {
        $this->artisan('texts:sync')->assertSuccessful();
        $n = count(json_decode(file_get_contents(base_path(SiteText::DATA)), true));
        $this->assertGreaterThan(500, $n);
        $this->assertSame($n, SiteText::count());

        $t = SiteText::orderBy('position')->first();
        $t->update(['proposed' => 'Nuevo título']);
        $this->artisan('texts:sync')->assertSuccessful();   // again: nothing doubled, nothing lost
        $this->assertSame($n, SiteText::count());
        $this->assertSame('Nuevo título', $t->fresh()->proposed);
    }

    public function test_nothing_without_the_pin(): void
    {
        $this->artisan('texts:sync');
        $t = SiteText::first();
        $this->get('/muestras/textos')->assertStatus(401)->assertDontSee($t->current_text);
        $this->postJson('/muestras/textos/' . $t->id, ['value' => 'x'])->assertStatus(401);
        $this->assertNull($t->fresh()->proposed);
        $this->assertSame(0, SiteTextEdit::count());
    }

    public function test_every_save_is_kept(): void
    {
        $this->artisan('texts:sync');
        $t = SiteText::where('current_text', 'Tu coche perfecto te espera')->firstOrFail();

        $page = $this->in()->get('/muestras/textos')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->getContent();
        $this->assertStringContainsString('Tu coche perfecto te espera', $page);
        $this->assertStringContainsString('<span class="ph">{precio}</span>', $page);
        $this->assertStringContainsString('<span class="tag">titlu Google</span>', $page);

        $this->in()->postJson('/muestras/textos/' . $t->id, ['value' => "El coche que buscas\r\n"])
            ->assertOk()->assertJson(['ok' => true, 'changed' => 1]);
        $this->in()->postJson('/muestras/textos/' . $t->id, ['value' => 'El coche que buscas'])->assertOk();   // the same: no new version
        $this->in()->postJson('/muestras/textos/' . $t->id, ['value' => 'Tu próximo coche'])->assertOk();
        $this->in()->postJson('/muestras/textos/' . $t->id, ['value' => ''])->assertOk()->assertJson(['changed' => 0]);

        $this->assertSame('', $t->fresh()->proposed);
        $this->assertSame(['El coche que buscas', 'Tu próximo coche', ''],
            SiteTextEdit::where('site_text_id', $t->id)->orderBy('id')->pluck('value')->all());

        $log = array_map('json_decode', array_filter(explode("\n", \Illuminate\Support\Facades\Storage::disk('local')->get(\App\Http\Controllers\SiteTextController::LOG))));
        $this->assertSame(['El coche que buscas', 'Tu próximo coche', ''], array_column($log, 'value'));

        $this->in()->postJson('/muestras/textos/' . $t->id, ['value' => str_repeat('a', 5001)])->assertStatus(422);
        $this->in()->postJson('/muestras/textos/999999', ['value' => 'x'])->assertNotFound();
    }

    public function test_what_he_writes_is_text_not_markup(): void
    {
        $this->artisan('texts:sync');
        $t = SiteText::first();
        $this->in()->postJson('/muestras/textos/' . $t->id, ['value' => '<script>alert(1)</script> 5 < 6'])->assertOk();
        $page = $this->in()->get('/muestras/textos')->getContent();
        $this->assertStringNotContainsString('<script>alert(1)', $page);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt; 5 &lt; 6', $page);
    }
}
