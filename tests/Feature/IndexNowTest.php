<?php

namespace Tests\Feature;

use App\Jobs\IndexNowPing;
use App\Models\Vehicle;
use App\Support\IndexNow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/** IndexNow (App\Support\IndexNow, VehicleObserver), 2026-10-29. */
class IndexNowTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_key_file_answers_with_the_key(): void
    {
        $this->assertSame(IndexNow::KEY, file_get_contents(public_path(IndexNow::KEY . '.txt')));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', IndexNow::KEY);
    }

    public function test_saving_a_car_queues_its_pages_and_a_seller_form_does_not(): void
    {
        Queue::fake();
        $car = Vehicle::create(['slug' => 'bmw-x1-2020-in', 'brand' => 'BMW', 'model' => 'X1', 'title' => 'BMW X1',
            'year' => 2020, 'price' => 25000, 'mileage' => 50000, 'status' => 'available']);
        Queue::assertPushed(IndexNowPing::class, fn ($j) => $j->urls === [
            url('/coche/bmw-x1-2020-in'), url('/catalogo/bmw'), url('/catalogo'), url('/'),
        ]);

        Queue::fake();
        $car = $car->fresh();                         // as the panel loads it: not "recently created"
        $car->update(['color' => 'Gris']);            // nothing a search result shows
        Queue::assertNotPushed(IndexNowPing::class);
        $car->update(['status' => 'sold']);
        Queue::assertPushed(IndexNowPing::class);

        Queue::fake();
        Vehicle::create(['slug' => 'te-lo-vendo', 'brand' => 'Audi', 'model' => 'A3', 'title' => 'Audi A3',
            'year' => 2015, 'price' => 0, 'mileage' => 1, 'status' => 'pending']);
        Queue::assertNotPushed(IndexNowPing::class);
    }

    public function test_it_posts_once_from_production_only(): void
    {
        Http::fake(['api.indexnow.org/*' => Http::response('', 202)]);
        $urls = [url('/catalogo'), url('/catalogo'), 'https://otro-sitio.com/x'];

        $this->assertSame(0, IndexNow::send($urls));             // not production
        Http::assertNothingSent();

        $this->app['env'] = 'production';
        $this->assertSame(202, IndexNow::send($urls));
        Http::assertSent(fn ($r) => $r->url() === 'https://api.indexnow.org/indexnow'
            && $r['key'] === IndexNow::KEY && $r['urlList'] === [url('/catalogo')]
            && $r['keyLocation'] === url('/' . IndexNow::KEY . '.txt'));
        $this->assertSame(0, IndexNow::send($urls));             // the same URL within 5 minutes: not again
        Http::assertSentCount(1);
    }
}
