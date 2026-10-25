<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Support\CochesNetSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The coches.net sheet (2026-10-22): read from the PDF coches.net PRO prints, into
 * the car form, saved with the car, shown on its page as "Su historia", and the A4
 * placard with its QR code. Fixtures: six real sheets of Iulian's
 * (tests/fixtures/coches-net). In-memory database and fake disks: nothing of the
 * real site is touched.
 */
class CochesNetSheetTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'Sh33tTok3nSh33tTok3nSh33tTok3nSh33tTok3n';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        Queue::fake();
        Http::fake(['i.ytimg.com/*' => Http::response('', 200)]);
    }

    private function fx(string $name): string
    {
        return base_path('tests/fixtures/coches-net/' . $name . '.pdf');
    }

    private function upload(string $name): UploadedFile
    {
        return new UploadedFile($this->fx($name), $name . '.pdf', 'application/pdf', null, true);
    }

    /* ------------------------------------------------------------- reading */

    public function test_every_sheet_is_read(): void
    {
        $want = [
            'golf'        => ['Volkswagen', 'Golf Sport 1.5 TSI EVO 110kW 150CV', 2017, 14990, 154000, 'Gasolina', 'Manual', '150'],
            'bmw-218d'    => ['BMW', 'Serie 2 Gran Tourer 218d xDrive', 2020, 19900, 134711, 'Diésel', 'Automático', '150'],
            'bmw-318d'    => ['BMW', 'Serie 3 318d', 2020, 21499, 125172, 'Diésel', 'Manual', '150'],
            'cla'         => ['Mercedes-Benz', 'Clase CLA CLA 200 d', 2018, 17990, 171000, 'Diésel', 'Automático', '136'],
            'transporter' => ['Volkswagen', 'Transporter Furgon Largo TN 2.0 TDI 110kW DSG', 2020, 24800, 184000, 'Diésel', 'Automático', '150'],
            'e350d'       => ['Mercedes-Benz', 'Clase E Cabrio E 350 d', 2016, 23700, 135401, 'Diésel', 'Automático', '258'],
        ];
        foreach ($want as $file => [$brand, $model, $year, $price, $km, $fuel, $gear, $cv]) {
            $r = CochesNetSheet::parse($this->fx($file));
            $this->assertTrue($r['ok'], $file);
            $f = $r['fields'];
            $this->assertSame([$brand, $model, $year, $price, $km, $fuel, $gear, $cv],
                [$f['brand'], $f['model'], $f['year'], $f['price'], $f['mileage'], $f['fuel'], $f['transmission'], $f['power']], $file);
            $this->assertSame('C', $r['sheet']['label'], $file);
            $this->assertNotEmpty($r['sheet']['description'], $file);
            $this->assertStringNotContainsString('coches.net se exonera', $r['sheet']['description'], $file);
            $this->assertStringNotContainsString('interesado?', $r['sheet']['description'], $file);
            $this->assertGreaterThan($price, $r['sheet']['new_total'], $file);
        }
    }

    public function test_the_extras_and_the_past_of_the_bmw_218d(): void
    {
        $r = CochesNetSheet::parse($this->fx('bmw-218d'));
        $s = $r['sheet'];
        $this->assertSame('20788694', $s['stock_id']);
        $this->assertCount(21, $s['extras']);
        $this->assertSame(['name' => 'Retrovisor interior, con ajuste automático anti-deslumbramiento', 'price' => 201.2], $s['extras'][12]);
        $this->assertSame(['name' => 'Control de distancia en aparcamiento (PDC) delantero y trasero', 'price' => 946.82], $s['extras'][17]);
        $this->assertEqualsWithDelta($s['extras_total'], array_sum(array_column($s['extras'], 'price')), 0.01);
        $this->assertSame([38250.0, 11740.6, 49990.6, 2020], [$s['new_price'], $s['extras_total'], $s['new_total'], $s['new_year']]);
        $this->assertSame([5, 5, 645, 123, 1995, 24], [$s['doors'], $s['seats'], $s['trunk'], $s['co2'], $s['cc'], $s['warranty_months']]);
        $this->assertSame(['registered' => '2020-06', 'itv' => '2028', 'keys' => 2, 'km' => 134711, 'no_accidents' => true], $s['facts']);
        // the sheet says 5 seats, the description "7 PLAZAS": he is asked to check
        $this->assertCount(1, $r['warnings']);
        $this->assertStringContainsString('5 plazas y la descripción 7', $r['warnings'][0]);
    }

    public function test_what_does_not_add_up_is_said(): void
    {
        $w = implode("\n", CochesNetSheet::parse($this->fx('bmw-318d'))['warnings']);
        $this->assertStringContainsString('la descripción dice «ECO»', $w);          // a mild hybrid
        $this->assertStringContainsString('Garantía: la ficha dice 2 años', $w);
        $this->assertStringContainsString('Año: la ficha dice 2020', $w);
    }

    public function test_a_sheet_without_some_fields_still_reads(): void
    {
        $r = CochesNetSheet::parse($this->fx('transporter'));      // no boot, title on two lines
        $this->assertNull($r['sheet']['trunk']);
        $this->assertSame('Furgoneta', $r['fields']['body_type']);
        $r = CochesNetSheet::parse($this->fx('cla'));              // no financed price
        $this->assertNull($r['sheet']['price_financed']);
        $this->assertSame(17990.0, $r['sheet']['price_cash']);
    }

    public function test_money_in_every_shape_the_sheet_writes_it(): void
    {
        $this->assertSame(14990.0, CochesNetSheet::money('14.990'));
        $this->assertSame(11740.6, CochesNetSheet::money('11.740,6'));
        $this->assertSame(414.23, CochesNetSheet::money('414.23'));
        $this->assertSame(1656.94, CochesNetSheet::money('1656.94'));
        $this->assertSame(0.0, CochesNetSheet::money('0'));
    }

    public function test_files_that_are_not_a_sheet_are_refused_in_words(): void
    {
        $dir = sys_get_temp_dir();
        file_put_contents($txt = "$dir/mc-not.pdf", 'hola');
        $this->assertStringContainsString('no es un PDF', CochesNetSheet::parse($txt)['error']);

        file_put_contents($other = "$dir/mc-other.pdf", $this->pdf('Factura 2026 de la luz. Total 84,20 euros. Gracias por su pago.'));
        $this->assertStringContainsString('No reconozco este PDF', CochesNetSheet::parse($other)['error']);

        file_put_contents($blank = "$dir/mc-blank.pdf", $this->pdf(''));
        $this->assertStringContainsString('no tiene texto', CochesNetSheet::parse($blank)['error']);
        @unlink($txt); @unlink($other); @unlink($blank);
    }

    /** a one-page PDF with one line of text (or none) */
    private function pdf(string $text): string
    {
        $stream = $text === '' ? '' : "BT /F1 12 Tf 40 780 Td ($text) Tj ET";
        $objs = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length ' . strlen($stream) . " >>\nstream\n$stream\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $out = "%PDF-1.4\n"; $off = [];
        foreach ($objs as $i => $o) { $off[] = strlen($out); $out .= ($i + 1) . " 0 obj\n$o\nendobj\n"; }
        $x = strlen($out);
        $out .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
        foreach ($off as $o) $out .= sprintf("%010d 00000 n \n", $o);
        return $out . "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n$x\n%%EOF";
    }

    /* ---------------------------------------------------- into the form, saved */

    public function test_the_form_reads_the_sheet_and_the_car_keeps_it(): void
    {
        $admin = User::factory()->create();
        $r = $this->actingAs($admin)->post(route('admin.sheets.read'), [
            'pdf' => $this->upload('bmw-218d'), 'token' => self::TOKEN, 'photos' => 0,
        ], ['Accept' => 'application/json']);
        $r->assertOk()
          ->assertJsonPath('fields.brand', 'BMW')
          ->assertJsonPath('fields.price', 19900)
          ->assertJsonPath('fields.vin', 'C')
          ->assertJsonPath('sheet.extras', 21)
          ->assertJsonPath('sheet.seats', 5)
          // what the review window shows before anything goes in the form
          ->assertJsonPath('sheet.extras_top.0', ['name' => 'Faros LED autoadaptables', 'price' => 1657])
          ->assertJsonPath('sheet.lines', ['Matriculado en junio de 2020', 'Sin accidentes', 'ITV hasta 2028', 'Dos llaves']);
        // a car with no photos gets the sheet's, staged like one from the phone
        $photo = $r->json('photo.path');
        $this->assertNotEmpty($photo);
        $key = $r->json('key');

        $this->actingAs($admin)->post(route('admin.vehicles.store'), [
            'brand' => 'BMW', 'model' => 'Serie 2 Gran Tourer 218d xDrive', 'year' => 2020, 'price' => 18700,
            'mileage' => 134711, 'vin' => 'C', 'photo_token' => self::TOKEN,
            'staged' => [['path' => $photo, 'tag' => 'exterior']],
            'sheet_key' => $key, 'sheet_seats' => '7', 'sheet_doors' => '5', 'sheet_trunk' => '645', 'sheet_co2' => '123', 'sheet_warranty' => '24',
        ])->assertRedirect();

        $car = Vehicle::firstOrFail();
        $this->assertSame('20788694', $car->sheet['stock_id']);
        $this->assertCount(21, $car->sheet['extras']);
        $this->assertSame(7, $car->sheet['seats']);                 // corrected by hand
        Storage::disk('local')->assertExists($car->sheet['pdf']);
        Storage::disk('local')->assertMissing('sheets/pending/' . $key . '.json');
        $this->assertNotEmpty($car->cover_image);

        // the car page: its past, the extra specs, two years of warranty
        $page = $this->get('/coche/' . $car->slug)->assertOk();
        $page->assertSee('Su historia')->assertSee('49.991 €')->assertSee('31.291 €')->assertSee('Faros LED autoadaptables')
             ->assertSee('Matriculado en junio de 2020')->assertSee('645 litros')
             ->assertSee('Con este coche van incluidos dos años');

        // the original PDF, for the admin
        $this->actingAs($admin)->get(route('admin.sheets.original', $car->slug))->assertOk();

        // saving again without a new sheet keeps it; "Quitar" removes it
        $this->actingAs($admin)->post(route('admin.vehicles.update', $car->slug), [
            'brand' => 'BMW', 'model' => 'Serie 2 Gran Tourer 218d xDrive', 'year' => 2020, 'price' => 18500,
        ])->assertRedirect();
        $this->assertCount(21, $car->fresh()->sheet['extras']);
        $this->actingAs($admin)->post(route('admin.vehicles.update', $car->slug), [
            'brand' => 'BMW', 'model' => 'Serie 2 Gran Tourer 218d xDrive', 'year' => 2020, 'price' => 18500, 'sheet_remove' => '1',
        ])->assertRedirect();
        $this->assertNull($car->fresh()->sheet);
        $this->get('/coche/' . $car->slug)->assertOk()->assertDontSee('Su historia');
    }

    public function test_a_bad_pdf_is_refused_with_a_sentence(): void
    {
        $admin = User::factory()->create();
        $bad = UploadedFile::fake()->createWithContent('foto.pdf', 'no soy un pdf');
        $this->actingAs($admin)->post(route('admin.sheets.read'), ['pdf' => $bad], ['Accept' => 'application/json'])
             ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'no es un PDF'));
    }

    public function test_the_same_car_already_in_the_list_is_pointed_out(): void
    {
        $admin = User::factory()->create();
        Vehicle::create(['slug' => 'bmw-218d-2020-x', 'brand' => 'Bmw', 'model' => '218d Gran Tourer', 'title' => 'x',
            'year' => 2020, 'price' => 18700, 'mileage' => 134000, 'status' => 'available']);
        $this->actingAs($admin)->post(route('admin.sheets.read'), ['pdf' => $this->upload('bmw-218d')], ['Accept' => 'application/json'])
             ->assertOk()->assertJsonCount(1, 'matches')->assertJsonPath('matches.0.name', 'BMW 218d Gran Tourer 2020');
    }

    public function test_a_car_without_a_sheet_is_untouched_by_saving(): void
    {
        $admin = User::factory()->create();
        $car = Vehicle::create(['slug' => 'audi-a3-2019-x', 'brand' => 'Audi', 'model' => 'A3', 'title' => 'x',
            'year' => 2019, 'price' => 15000, 'status' => 'available']);
        $this->actingAs($admin)->post(route('admin.vehicles.update', $car->slug), [
            'brand' => 'Audi', 'model' => 'A3', 'year' => 2019, 'price' => 15000, 'sheet_warranty' => '12',
        ])->assertRedirect();
        $this->assertNull($car->fresh()->sheet);
        $this->get('/coche/' . $car->slug)->assertOk()->assertSee('Un año va incluido');
    }

    /* ------------------------------------------------------------- the placard */

    public function test_the_placard_shows_todays_data_and_a_qr_code(): void
    {
        $admin = User::factory()->create();
        $sheet = CochesNetSheet::parse($this->fx('bmw-218d'))['sheet'];
        $car = Vehicle::create(['slug' => 'bmw-218d-2020-p', 'brand' => 'BMW', 'model' => 'Serie 2 Gran Tourer', 'title' => 'x',
            'year' => 2020, 'price' => 18700, 'original_price' => 20000, 'mileage' => 134711, 'fuel' => 'Diésel',
            'transmission' => 'Automático', 'power' => '150', 'vin' => 'C', 'status' => 'available', 'sheet' => $sheet]);

        $this->get(route('admin.vehicles.placard', $car->slug))->assertRedirect();       // admin only
        $this->actingAs($admin)->get(route('admin.vehicles.placard', $car->slug))->assertOk()
             ->assertSee('18.700 €')->assertSee('20.000 €')->assertSee('134.711 km')
             ->assertSee('49.991 €')->assertSee('Faros LED autoadaptables')->assertSee('<svg', false)
             ->assertSee('Precio al contado')->assertSee('2 años')->assertSee('24 meses')
             ->assertSee('Equipamiento extra')->assertSee('Desglose de la oferta')->assertSee('Matriculado en junio de 2020')
             ->assertSee('data-ratio', false)->assertSee('Imprimir')->assertSee('Descargar PDF');

        // the financed price was read at another cash price: not printed once the price moved
        $this->assertNull(\App\Http\Controllers\Admin\SheetController::placardData($car, false)['financed']);

        $pdf = $this->actingAs($admin)->get(route('admin.vehicles.placard-pdf', $car->slug));
        $pdf->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        // twenty-one extras, the offer and his whole description: still one sheet
        $this->assertSame(1, preg_match_all('~/Type\s*/Page[^s]~', $pdf->getContent()));
        $this->assertStringContainsString('attachment; filename="IV-Motorclass-bmw-serie-2-gran-tourer-2020.pdf"', $pdf->headers->get('Content-Disposition'));
    }
}
