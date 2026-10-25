<?php

namespace App\Support;

use App\Http\Controllers\HomePageController;
use App\Models\Vehicle;
use Illuminate\Support\Str;

/**
 * What search engines and answer engines (Google, its AI Overviews, ChatGPT,
 * Perplexity) read about a page: one JSON-LD graph in the <head>, written by
 * layouts/site.blade.php.
 *
 * Every page carries the dealer (AutoDealer) and the site (WebSite), and its own
 * WebPage pointing at both by @id, so a machine reading any page knows who is
 * selling, where and how to reach him. A page adds what it is about with add():
 * a car (Car + Offer), the catalogue (ItemList), the home page's questions
 * (FAQPage), and a breadcrumb.
 *
 * THE RULE: nothing here that the visitor cannot read on the site. Every value
 * comes from the database or from words already on a page — the footer, the meta
 * description, the FAQ. No rating, no review count, no opening hours (there are
 * none: "Todos los días · a convenir"), no street address (there is no shop:
 * "Málaga, sin tienda: trabajo con cita"), no VIN (the `vin` column holds the
 * DGT label). Structured data that says more than the page is what Google's
 * guidelines penalise, and an answer engine repeating it would be repeating
 * something untrue.
 */
final class Schema
{
    public const PHONE = '+34614753187';
    public const EMAIL = 'jvmotorclass@gmail.com';

    /** @var list<array<string,mixed>> nodes a page added while it rendered */
    private static array $nodes = [];

    /** @var array{type:string,crumbs:list<array{0:string,1:string}>} */
    private static array $page = ['type' => 'WebPage', 'crumbs' => []];

    /**
     * What kind of page this is (WebPage, AboutPage, ContactPage, CollectionPage,
     * ItemPage) and its breadcrumb, as [name, path] pairs after "Inicio".
     *
     * @param list<array{0:string,1:string}> $crumbs
     */
    public static function page(string $type, array $crumbs = []): void
    {
        self::$page = ['type' => $type, 'crumbs' => $crumbs];
    }

    /** @param array<string,mixed> $node */
    public static function add(array $node): void
    {
        self::$nodes[] = $node;
    }

    /**
     * The whole graph for this request, then forgotten (a worker that serves the
     * next request must not carry this one's car).
     */
    public static function render(string $title, string $description, string $url, string $image): string
    {
        $home = url('/') . '/';
        $page = [
            '@type'       => self::$page['type'],
            '@id'         => $url . '#webpage',
            'url'         => $url,
            'name'        => $title,
            'description' => $description,
            'inLanguage'  => 'es-ES',
            'isPartOf'    => ['@id' => $home . '#website'],
            'about'       => ['@id' => $home . '#dealer'],
            'primaryImageOfPage' => ['@type' => 'ImageObject', 'url' => $image],
        ];
        $graph = [self::dealer(), self::website()];

        if (self::$page['crumbs']) {
            $items = [['Inicio', '/'], ...self::$page['crumbs']];
            $graph[] = [
                '@type' => 'BreadcrumbList',
                '@id'   => $url . '#breadcrumb',
                'itemListElement' => array_map(fn ($c, $i) => [
                    '@type'    => 'ListItem',
                    'position' => $i + 1,
                    'name'     => $c[0],
                    'item'     => $c[1] === '/' ? $home : url($c[1]),
                ], $items, array_keys($items)),
            ];
            $page['breadcrumb'] = ['@id' => $url . '#breadcrumb'];
        }

        $graph[] = $page;
        foreach (self::$nodes as $n) {
            $graph[] = $n;
        }
        self::$nodes = [];
        self::$page = ['type' => 'WebPage', 'crumbs' => []];

        // JSON_HEX_TAG: a "</script>" typed into a car's description cannot end the block.
        return json_encode(['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    }

    /** The business: the footer's facts. */
    private static function dealer(): array
    {
        $home = url('/') . '/';

        return [
            '@type'       => 'AutoDealer',
            '@id'         => $home . '#dealer',
            'name'        => 'IV MOTORCLASS',
            'url'         => $home,
            'logo'        => ['@type' => 'ImageObject', 'url' => url('/img/brand/iv-motorclass.png'), 'width' => 525, 'height' => 539],
            'image'       => url('/img/og-default.jpg'),
            // the footer's own sentence
            'description' => 'Coches alemanes premium en Málaga. Cinco marcas, y sólo esas.',
            'telephone'   => self::PHONE,
            'email'       => self::EMAIL,
            'address'     => ['@type' => 'PostalAddress', 'addressLocality' => 'Málaga', 'addressCountry' => 'ES'],
            'areaServed'  => ['@type' => 'City', 'name' => 'Málaga'],
            'contactPoint' => [
                '@type'       => 'ContactPoint',
                'telephone'   => self::PHONE,
                'email'       => self::EMAIL,
                'contactType' => 'sales',
                'availableLanguage' => 'es',
            ],
            'brand'  => array_map(fn ($m) => ['@type' => 'Brand', 'name' => $m['name']], Marques::ALL),
            'sameAs' => array_values(array_map(fn ($s) => $s['url'], HomePageController::SOCIAL)),
        ];
    }

    private static function website(): array
    {
        $home = url('/') . '/';

        return [
            '@type'     => 'WebSite',
            '@id'       => $home . '#website',
            'url'       => $home,
            'name'      => 'IV MOTORCLASS',
            'inLanguage' => 'es-ES',
            'publisher' => ['@id' => $home . '#dealer'],
        ];
    }

    /**
     * One car, as the car page shows it: Car (a Product and a Vehicle) with its
     * Offer. A sold car stays a record with its price, as the page does, and says
     * SoldOut.
     *
     * @param list<string> $photos stored paths, in the page's order
     */
    public static function car(Vehicle $car, string $brand, array $photos, int $warrantyMonths, ?int $before): array
    {
        $url = url('/coche/' . $car->slug);
        $home = url('/') . '/';
        $sheet = is_array($car->sheet) ? $car->sheet : [];
        $num = fn ($v) => ($n = (int) preg_replace('/\D+/', '', (string) $v)) > 0 ? $n : null;
        $images = array_values(array_filter(array_map(
            fn ($p) => ($u = Img::url($p, 1080)) ? url($u) : null,
            array_slice($photos, 0, 8)
        )));
        $about = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $car->description)));
        $availability = match ($car->status) {
            'sold'     => 'https://schema.org/SoldOut',
            'reserved' => 'https://schema.org/Reserved',
            default    => 'https://schema.org/InStock',
        };

        $offer = array_filter([
            '@type'         => 'Offer',
            'url'           => $url,
            'price'         => (int) $car->price ?: null,
            'priceCurrency' => 'EUR',
            'availability'  => $availability,
            'itemCondition' => 'https://schema.org/UsedCondition',
            'seller'        => ['@id' => $home . '#dealer'],
            'areaServed'    => ['@type' => 'City', 'name' => 'Málaga'],
            'priceSpecification' => $before ? [
                '@type' => 'UnitPriceSpecification',
                'priceType' => 'https://schema.org/StrikethroughPrice',
                'price' => $before,
                'priceCurrency' => 'EUR',
            ] : null,
            'warranty' => $car->status === 'sold' ? null : [
                '@type' => 'WarrantyPromise',
                'durationOfWarranty' => ['@type' => 'QuantitativeValue', 'value' => $warrantyMonths, 'unitCode' => 'MON'],
            ],
        ], fn ($v) => $v !== null);

        $engine = array_filter([
            '@type' => 'EngineSpecification',
            'name'  => $car->engine ?: $car->engine_capacity,
            'fuelType' => self::tidy($car->fuel ?: $car->fuel_type),
            'enginePower' => ($p = $num($car->power)) ? ['@type' => 'QuantitativeValue', 'value' => $p, 'unitText' => 'CV'] : null,
        ], fn ($v) => $v !== null && $v !== '');

        return array_filter([
            '@type'       => 'Car',
            '@id'         => $url . '#car',
            'name'        => trim($brand . ' ' . $car->model . ' ' . $car->year),
            'url'         => $url,
            'image'       => $images ?: null,
            'description' => mb_strlen($about) >= 40 ? Str::limit($about, 4900, '…') : null,
            'brand'       => ['@type' => 'Brand', 'name' => $brand],
            'model'       => $car->model ?: null,
            'vehicleModelDate' => $car->year ? (string) $car->year : null,
            'mileageFromOdometer' => $car->mileage ? ['@type' => 'QuantitativeValue', 'value' => (int) $car->mileage, 'unitCode' => 'KMT'] : null,
            'fuelType'    => self::tidy($car->fuel ?: $car->fuel_type),
            'vehicleTransmission' => self::tidy($car->transmission),
            'vehicleEngine' => count($engine) > 1 ? $engine : null,
            'bodyType'    => $car->body_type ?: null,
            'driveWheelConfiguration' => $car->drivetrain ?: null,
            'color'       => $car->color ?: null,
            'numberOfDoors' => $num($sheet['doors'] ?? null),
            'seatingCapacity' => $num($sheet['seats'] ?? null),
            'cargoVolume' => ($t = $num($sheet['trunk'] ?? null)) ? ['@type' => 'QuantitativeValue', 'value' => $t, 'unitCode' => 'LTR'] : null,
            'emissionsCO2' => $num($sheet['co2'] ?? null),
            'itemCondition' => 'https://schema.org/UsedCondition',
            'offers'      => $offer,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * The cars for sale, in the order a page lists them.
     *
     * @param iterable<Vehicle> $cars
     */
    public static function carList(iterable $cars, string $name): array
    {
        $items = [];
        foreach ($cars as $c) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => count($items) + 1,
                'url'      => url('/coche/' . $c->slug),
                'name'     => trim((Marques::for($c->brand)['name'] ?? Str::title((string) $c->brand)) . ' ' . $c->model . ' ' . $c->year),
            ];
        }

        return [
            '@type' => 'ItemList',
            'name'  => $name,
            'numberOfItems' => count($items),
            'itemListElement' => $items,
        ];
    }

    /**
     * Questions and answers exactly as a page prints them.
     *
     * @param list<array{0:string,1:string}> $qa
     */
    public static function faq(array $qa): array
    {
        return [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($x) => [
                '@type' => 'Question',
                'name'  => $x[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $x[1]],
            ], $qa),
        ];
    }

    /** The data's own spellings ("diesel", "automatica") as the pages print them. */
    private static function tidy(?string $v): ?string
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        $map = ['diesel' => 'Diésel', 'diésel' => 'Diésel', 'gasolina' => 'Gasolina',
                'automatica' => 'Automático', 'automática' => 'Automático', 'automático' => 'Automático',
                'manual' => 'Manual'];

        return $map[mb_strtolower($v)] ?? $v;
    }
}
