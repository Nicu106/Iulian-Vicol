<?php

namespace Tests\Feature;

use App\Http\Controllers\HomePageController;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * What machines read (2026-10-25): the JSON-LD graph in every page's head
 * (App\Support\Schema), /sitemap.xml with the photographs and /llms.txt
 * (App\Http\Controllers\SeoController). The rule they all keep: nothing the page
 * itself does not say.
 */
class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Queue::fake();
        Http::fake();
    }

    private function car(array $attrs = []): Vehicle
    {
        $slug = $attrs['slug'] ?? 'bmw-320d-2019-seo';
        Storage::disk('public')->put("vehicles/$slug/a.jpg", 'x');

        return Vehicle::create(array_merge([
            'slug' => $slug, 'brand' => 'Bmw', 'model' => '320d Touring', 'title' => 'BMW 320d',
            'year' => 2019, 'price' => 23900, 'original_price' => 25900, 'mileage' => 98000,
            'fuel' => 'Diesel', 'transmission' => 'Automático', 'power' => '190cv', 'status' => 'available',
            'vin' => 'C', // the DGT label lives in this column: never a VIN in the data
            'cover_image' => "/storage/vehicles/$slug/a.jpg", 'gallery_images' => ["/storage/vehicles/$slug/a.jpg"],
            'description' => 'Un coche cuidado, con todas sus revisiones en la casa oficial y dos llaves.',
        ], $attrs));
    }

    /** @return list<array<string,mixed>> the page's @graph */
    private function graph(string $html): array
    {
        $this->assertSame(1, preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m));
        $data = json_decode($m[1][0], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.org', $data['@context']);

        return $data['@graph'];
    }

    private function node(array $graph, string $type): array
    {
        foreach ($graph as $n) {
            // a node may carry several types (a car is ['Product', 'Car'])
            if (in_array($type, (array) $n['@type'], true)) {
                return $n;
            }
        }
        $this->fail("no $type in " . json_encode(array_column($graph, '@type')));
    }

    public function test_a_car_page_describes_the_car_and_its_offer(): void
    {
        $car = $this->car();
        $g = $this->graph($this->get('/coche/' . $car->slug)->assertOk()->getContent());

        $c = $this->node($g, 'Car');
        $this->assertSame(['Product', 'Car'], $c['@type']);   // Google reads a car as a product only so
        $this->assertSame('BMW 320d Touring 2019', $c['name']);              // the marque's spelling, not "Bmw"
        $this->assertSame(['@type' => 'QuantitativeValue', 'value' => 98000, 'unitCode' => 'KMT'], $c['mileageFromOdometer']);
        $this->assertSame(190, $c['vehicleEngine']['enginePower']['value']);
        $this->assertSame(23900, $c['offers']['price']);
        $this->assertSame('EUR', $c['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $c['offers']['availability']);
        $this->assertSame(25900, $c['offers']['priceSpecification']['price']);   // "antes", as the page shows
        $this->assertArrayNotHasKey('vehicleIdentificationNumber', $c);
        $this->assertStringNotContainsString('aggregateRating', json_encode($g));

        $crumbs = $this->node($g, 'BreadcrumbList')['itemListElement'];
        $this->assertSame(['Inicio', 'Coches', 'BMW 320d Touring 2019'], array_column($crumbs, 'name'));

        // every reference points at a node of the same graph
        $ids = array_filter(array_column($g, '@id'));
        preg_match_all('~\{"@id":"([^"]+)"\}~', json_encode($g, JSON_UNESCAPED_SLASHES), $refs);
        foreach ($refs[1] as $r) {
            $this->assertContains($r, $ids);
        }
    }

    public function test_a_sold_car_says_so(): void
    {
        $car = $this->car(['status' => 'sold']);
        $c = $this->node($this->graph($this->get('/coche/' . $car->slug)->getContent()), 'Car');
        $this->assertSame('https://schema.org/SoldOut', $c['offers']['availability']);
        $this->assertArrayNotHasKey('warranty', $c['offers']);
    }

    public function test_the_home_page_questions_are_the_ones_it_prints(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $faq = $this->node($this->graph($html), 'FAQPage')['mainEntity'];
        $this->assertCount(count(HomePageController::FAQ), $faq);
        foreach (HomePageController::FAQ as $i => [$q, $a]) {
            $this->assertSame($q, $faq[$i]['name']);
            $this->assertSame($a, $faq[$i]['acceptedAnswer']['text']);
            $this->assertStringContainsString('<summary>' . e($q) . '</summary><p>' . e($a) . '</p>', $html);
        }
    }

    public function test_the_sitemap_and_llms_txt_list_the_cars(): void
    {
        $car = $this->car();
        $this->car(['slug' => 'audi-a3-2015-pending', 'status' => 'pending']);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertNotFalse(simplexml_load_string($xml));
        $this->assertStringContainsString('<loc>' . url('/coche/' . $car->slug) . '</loc>', $xml);
        $this->assertStringContainsString('<image:image><image:loc>', $xml);
        $this->assertStringNotContainsString('pending', $xml);              // an unapproved car does not exist

        $txt = $this->get('/llms.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->getContent();
        $this->assertStringStartsWith("# IV MOTORCLASS\n", $txt);
        $this->assertStringContainsString('[BMW 320d Touring 2019](' . url('/coche/' . $car->slug) . '): 23.900 € · 98.000 km', $txt);
        $this->assertStringNotContainsString('pending', $txt);
    }

    public function test_a_page_kept_out_of_the_index_carries_no_graph(): void
    {
        $html = $this->get('/coche/no-existe')->assertNotFound()->getContent();
        $this->assertStringNotContainsString('application/ld+json', $html);
    }

    /** /catalogo/bmw: titled for the search, self-canonical, only its marque, in the sitemap (2026-10-29). */
    public function test_a_marque_has_its_own_page(): void
    {
        $this->car();
        $this->car(['slug' => 'vw-golf-2018-seo', 'brand' => 'Volkswagen', 'model' => 'Golf', 'title' => 'VW Golf']);

        $html = $this->get('/catalogo/bmw')->assertOk()->getContent();
        $this->assertStringContainsString('<title>BMW de segunda mano en Málaga — IV MOTORCLASS</title>', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/catalogo/bmw') . '">', $html);
        $this->assertStringContainsString('BMW de segunda mano</h1>', $html);
        $this->assertStringContainsString('1 BMW de segunda mano a la venta en Málaga, desde 23.900 €', $html);
        $this->assertStringContainsString('/coche/bmw-320d-2019-seo', $html);
        $this->assertStringNotContainsString('/coche/vw-golf-2018-seo', $html);
        $this->assertStringNotContainsString('panamera', $html);   // no other marque's photograph
        $list = $this->node($this->graph($html), 'ItemList');
        $this->assertSame(1, $list['numberOfItems']);

        // nothing to show: out of the index, out of the sitemap
        $this->assertStringContainsString('content="noindex, follow"', $this->get('/catalogo/porsche')->assertOk()->getContent());
        $this->get('/catalogo/seat')->assertNotFound();
        $map = $this->get('/sitemap.xml')->getContent();
        $this->assertStringContainsString('<loc>' . url('/catalogo/bmw') . '</loc>', $map);
        $this->assertStringNotContainsString('/catalogo/porsche', $map);

        // the whole catalogue leads to each marque's page; a shared link keeps its short title
        $all = $this->get('/catalogo')->getContent();
        $this->assertStringContainsString('href="/catalogo/bmw"', $all);
        $this->assertStringContainsString('<meta property="og:title" content="Catálogo — IV MOTORCLASS">', $all);
    }
}
