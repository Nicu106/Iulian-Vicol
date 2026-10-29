<?php

namespace App\Support;

/**
 * The optional maintenance plan offered with a car: oil, filters and whatever
 * is due, at a closed price, for one or two years. Like the warranty
 * (App\Support\Warranty), each car shows the standard prices or the ones he set
 * for it in the panel — `sheet.maintenance_prices`, {"12": 250, "24": null};
 * an empty one is "Pregúntame" on the page.
 */
final class Maintenance
{
    /** The standard prices, by months. */
    public const PRICES = [12 => 200, 24 => 400];

    public static function own(?array $sheet): ?array
    {
        return Warranty::prices($sheet, 'maintenance_prices', self::PRICES);
    }

    /**
     * The steps the car page shows: [years, '200 €' | 'Pregúntame'].
     *
     * @return list<array{0:int,1:string}>
     */
    public static function ladder(?array $sheet): array
    {
        $own = self::own($sheet);
        $steps = [];
        foreach (self::PRICES as $m => $std) {
            $price = $own !== null ? $own[$m] : $std;
            $steps[] = [intdiv($m, 12), $price ? Warranty::euros($price) : 'Pregúntame'];
        }

        return $steps;
    }
}
