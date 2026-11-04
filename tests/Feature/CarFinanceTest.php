<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Support\Finance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * The instalment calculator (App\Support\Finance and partials/car-fin),
 * 2026-11-04. The sums are held to a lender's own published example; the page
 * to what the client asked for: 6.99 %, six terms, five years chosen.
 */
class CarFinanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Http::fake();
    }

    public function test_the_sums(): void
    {
        // coches.net with Lendrock: 17.999 € + 3 % fee = 18.538,97 € at 6,99 % over 72 months → 315,98 €
        $this->assertSame(315.98, Finance::monthly(18538.97, 72));
        $this->assertSame(7.22, Finance::tae());

        $plans = Finance::plans(24800);
        $this->assertSame([6, 12, 24, 36, 48, 60], array_column($plans, 'months'));
        $this->assertSame(['6 meses', '1 año', '2 años', '3 años', '4 años', '5 años'], array_column($plans, 'label'));
        $this->assertSame(490.95, end($plans)['monthly']);
        $this->assertSame(29457.0, end($plans)['total']);
        // the longest term is the lowest instalment: the one chosen when the page opens
        $this->assertSame(min(array_column($plans, 'monthly')), end($plans)['monthly']);

        $this->assertSame(611.28, Finance::plans(24800, 5000)[3]['monthly']);
        $this->assertSame(0.0, Finance::plans(24800, 30000)[5]['monthly']);
        $this->assertSame('29.457,00 €', Finance::money(29457.0));
        $this->assertSame('491 €', Finance::money(490.95, false));
        $this->assertSame('7,22 %', Finance::pct(Finance::tae()));
    }

    public function test_the_car_page(): void
    {
        $car = Vehicle::create(['slug' => 'vw-transporter-2021-fin', 'brand' => 'Volkswagen', 'model' => 'Transporter', 'title' => 'VW Transporter',
            'year' => 2021, 'price' => 24800, 'mileage' => 184000, 'status' => 'available']);
        $page = $this->get('/coche/' . $car->slug)->assertOk()->getContent();

        // under the price
        $this->assertMatchesRegularExpression('~class="car-fin-hint" href="#financiacion"[^>]*>o <b>491 €</b> al mes\s*<span>en 5 años · TAE 7,22 %</span>~', $page);
        // the calculator: six terms, five years checked, its figures with the TAE, the TIN and the total
        $this->assertStringContainsString('id="financiacion"', $page);
        $this->assertSame(6, substr_count($page, 'type="radio" name="fin-n"'));
        $this->assertMatchesRegularExpression('~value="60"[^>]*checked~', $page);
        $this->assertSame(1, preg_match_all('~type="radio" name="fin-n"[^>]*checked~', $page));
        $this->assertStringContainsString('60 cuotas de 490,95 €.', $page);
        $this->assertStringContainsString('TAE 7,22 %, TIN 6,99 %.', $page);
        $this->assertStringContainsString('total a pagar 29.457,00 €.', $page);
        $this->assertStringContainsString('Cálculo orientativo, sin comisión de apertura.', $page);
        $this->assertStringContainsString('Quiero financiarlo', $page);

        // a car that is gone sells nothing on credit
        $car->update(['status' => 'sold']);
        $page = $this->get('/coche/' . $car->slug)->assertOk()->getContent();
        $this->assertStringNotContainsString('financiacion', $page);
        $this->assertStringNotContainsString('car-fin', $page);
    }
}
