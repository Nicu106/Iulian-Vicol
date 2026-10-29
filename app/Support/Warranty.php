<?php

namespace App\Support;

/**
 * The warranty of one car: how long comes with it, and what more time costs.
 *
 * Both live in the car's `sheet` column (Admin\SheetController::adopt):
 *   warranty_months   12, 24 or 36 — included in the price (one year at least)
 *   warranty_prices   absent: the standard list below. Present: his own prices
 *                     for THIS car, {"24": 750, "36": null}; an empty one is
 *                     "Pregúntame" on the page.
 *
 * The standard list is the price of going from the one included year to two or
 * three. From two included years the third has no standard price: it says
 * "Pregúntame" unless he set one for the car.
 */
final class Warranty
{
    /** The standard prices, by months of warranty in total. */
    public const PRICES = [24 => 600, 36 => 900];

    public static function months(?array $sheet): int
    {
        return min(36, max(12, (int) ($sheet['warranty_months'] ?? 12)));
    }

    /** His own prices for this car, or null when the standard list applies. */
    public static function own(?array $sheet): ?array
    {
        return self::prices($sheet, 'warranty_prices', self::PRICES);
    }

    /**
     * A plan's own prices from the sheet (warranty_prices, maintenance_prices),
     * keyed by months like its standard list; null when it has none.
     *
     * @param array<int,int> $std
     */
    public static function prices(?array $sheet, string $key, array $std): ?array
    {
        $p = $sheet[$key] ?? null;
        if (!is_array($p)) {
            return null;
        }
        $out = [];
        foreach (array_keys($std) as $m) {
            $v = $p[$m] ?? $p[(string) $m] ?? null;
            $out[$m] = $v === null || $v === '' ? null : (int) $v;
        }

        return $out;
    }

    public static function euros(int $n): string
    {
        return number_format($n, 0, ',', '.') . ' €';
    }

    /**
     * The steps the car page shows, from the included one up to three years:
     * [years, 'Incluido' | '600 €' | 'Pregúntame', included?].
     *
     * @return list<array{0:int,1:string,2:bool}>
     */
    public static function ladder(?array $sheet): array
    {
        $inc = intdiv(self::months($sheet), 12);
        $own = self::own($sheet);
        $eur = fn (int $n) => self::euros($n);
        $steps = [];
        for ($y = $inc; $y <= 3; $y++) {
            if ($y === $inc) {
                $steps[] = [$y, 'Incluido', true];
                continue;
            }
            $price = $own !== null ? $own[$y * 12] : ($inc === 1 ? self::PRICES[$y * 12] : null);
            $steps[] = [$y, $price ? $eur($price) : 'Pregúntame', false];
        }

        return $steps;
    }
}
