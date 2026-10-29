<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\SheetController;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\Maintenance;
use App\Support\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Warranty and maintenance prices per car (App\Support\Warranty, Maintenance,
 * Admin\SheetController::adopt), 2026-10-30. With MC_DUMP=/dir the car form is
 * written there to be photographed.
 */
class CarPlansTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Http::fake();
    }

    private function adopt(array $in, ?array $current = null): ?array
    {
        $base = ['sheet_doors' => '', 'sheet_seats' => '', 'sheet_trunk' => '', 'sheet_co2' => '', 'sheet_warranty' => '12'];

        return SheetController::adopt(Request::create('/x', 'POST', $in + $base), 'x', $current);
    }

    public function test_standard_keeps_nothing_and_custom_keeps_his_prices(): void
    {
        // both standard on a car without a sheet: no sheet
        $this->assertNull($this->adopt(['warranty_mode' => 'std', 'maintenance_mode' => 'std']));

        // his maintenance prices, one left empty
        $s = $this->adopt(['warranty_mode' => 'std', 'maintenance_mode' => 'own', 'maintenance_12' => '250 €', 'maintenance_24' => '']);
        $this->assertSame([12 => 250, 24 => null], $s['maintenance_prices']);
        $this->assertArrayNotHasKey('warranty_prices', $s);
        $this->assertSame([[1, '250 €'], [2, 'Pregúntame']], Maintenance::ladder(json_decode(json_encode($s), true)));

        // back to standard: the key goes
        $s = $this->adopt(['warranty_mode' => 'std', 'maintenance_mode' => 'std'], $s);
        $this->assertArrayNotHasKey('maintenance_prices', $s);
        $this->assertSame([[1, '200 €'], [2, '400 €']], Maintenance::ladder($s));

        // both custom at once
        $s = $this->adopt(['warranty_mode' => 'own', 'warranty_24' => '700', 'warranty_36' => '1.000',
                           'maintenance_mode' => 'own', 'maintenance_12' => '180', 'maintenance_24' => '350']);
        $this->assertSame([24 => 700, 36 => 1000], $s['warranty_prices']);
        $this->assertSame([12 => 180, 24 => 350], $s['maintenance_prices']);
        $this->assertSame([[1, 'Incluido', true], [2, '700 €', false], [3, '1.000 €', false]], Warranty::ladder($s));
    }

    public function test_the_car_page_and_the_form(): void
    {
        $car = Vehicle::create(['slug' => 'audi-a4-2019-pl', 'brand' => 'Audi', 'model' => 'A4', 'title' => 'Audi A4',
            'year' => 2019, 'price' => 21000, 'mileage' => 80000, 'status' => 'available',
            'sheet' => ['source' => 'manual', 'warranty_months' => 12, 'maintenance_prices' => ['12' => 250, '24' => null]]]);

        $page = $this->get('/coche/' . $car->slug)->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~Mantenimiento.*?1 año</span>\s*<span class="car-off__p">250 €.*?2 años</span>\s*<span class="car-off__p">Pregúntame~s', $page);
        $this->assertMatchesRegularExpression('~2 años</span>\s*<span class="car-off__p">600 €~', $page);   // the warranty stays standard

        $form = $this->actingAs(User::factory()->create())->get('/admin/vehicles/' . $car->slug . '/edit')->assertOk()->getContent();
        $this->assertStringContainsString('Garantía y mantenimiento', $form);
        $this->assertMatchesRegularExpression('~name="maintenance_mode" value="own" checked~', $form);
        $this->assertMatchesRegularExpression('~name="warranty_mode" value="std" checked~', $form);
        $this->assertStringContainsString('name="maintenance_12" value="250"', $form);
        $this->assertStringContainsString('1 año 200 € · 2 años 400 €', $form);
        $this->assertSame(1, substr_count($form, 'name="sheet_warranty"'));
        if ($dir = getenv('MC_DUMP')) {
            file_put_contents($dir . '/plans-edit.html', $form);
        }
    }
}
