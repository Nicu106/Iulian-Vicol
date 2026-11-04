<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Support\CarPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * The car as a PDF (App\Support\CarPdf, views/pdf/ficha), 2026-11-04: what it
 * says, and where the button is. Chrome itself is not run here; the live
 * check is curl on /coche/<slug>/ficha.pdf.
 */
class CarPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Http::fake();
    }

    private function car(array $over = []): Vehicle
    {
        return Vehicle::create($over + ['slug' => 'vw-transporter-2021-pdf', 'brand' => 'Volkswagen', 'model' => 'Transporter Larga',
            'title' => 'VW Transporter', 'year' => 2021, 'price' => 24800, 'original_price' => 25000, 'mileage' => 184000,
            'status' => 'available', 'fuel' => 'Diesel', 'transmission' => 'Automático', 'power' => '150',
            'description' => "Una furgoneta muy cuidada.\nRevisada.\n\nEquipamiento destacado:\n? Cambio DSG.\n? Cámara de marcha atrás.\n?\n\nNO SE ACEPTAN CAMBIOS.",
            'features' => ['Apple CarPlay', 'Android Auto']]);
    }

    public function test_the_description_keeps_his_lists(): void
    {
        $this->assertSame([
            ['p', "Una furgoneta muy cuidada.\nRevisada."],
            ['p', 'Equipamiento destacado:'],
            ['ul', ['Cambio DSG.', 'Cámara de marcha atrás.']],
            ['p', 'NO SE ACEPTAN CAMBIOS.'],
        ], CarPdf::about("Una furgoneta muy cuidada.\nRevisada.\n\nEquipamiento destacado:\n? Cambio DSG.\n? Cámara de marcha atrás.\n?\n\nNO SE ACEPTAN CAMBIOS."));
    }

    public function test_what_the_pdf_says(): void
    {
        $html = view('pdf.ficha', CarPdf::data($this->car()))->render();
        $this->assertStringContainsString('Volkswagen Transporter Larga <span>2021</span>', $html);
        $this->assertStringContainsString('<b>24.800 €</b>', $html);
        $this->assertStringContainsString('antes <s>25.000 €</s> <em>−200 €</em>', $html);
        $this->assertStringContainsString('o 491 € al mes', $html);
        $this->assertStringContainsString('en 5 años · TAE 7,22 %', $html);
        $this->assertStringContainsString('60 cuotas de 490,95 €. TAE 7,22 %, TIN 6,99 %.', $html);
        $this->assertStringContainsString('<li>Cámara de marcha atrás.</li>', $html);
        $this->assertStringContainsString('<li>Apple CarPlay</li>', $html);
        $this->assertStringContainsString('<h2>Garantía</h2>', $html);
        $this->assertStringContainsString('<h2>Mantenimiento</h2>', $html);
        $this->assertSame(9, substr_count($html, '<img src="data:image/svg+xml;base64,'));   // the nine lenders
        $this->assertStringNotContainsString('? ', strip_tags($html));
    }

    public function test_the_button_and_who_gets_one(): void
    {
        // the button lives in the row under the photograph: a car with one
        $car = $this->car(['cover_image' => '/storage/vehicles/test-pdf.jpg', 'gallery_images' => ['/storage/vehicles/test-pdf.jpg']]);
        $page = $this->get('/coche/' . $car->slug)->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~<a class="car-pdf" href="[^"]*/coche/vw-transporter-2021-pdf/ficha\.pdf" download="IV-MOTORCLASS-volkswagen-transporter-larga-2021\.pdf" data-stat="pdf">~', $page);

        // a car that is gone has no PDF to offer, and the address says so
        $car->update(['status' => 'sold']);
        $this->assertStringNotContainsString('car-pdf', $this->get('/coche/' . $car->slug)->getContent());
        $this->get('/coche/' . $car->slug . '/ficha.pdf')->assertNotFound();
        $this->get('/coche/no-existe/ficha.pdf')->assertNotFound();
    }
}
