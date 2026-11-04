<?php

namespace App\Support;

/**
 * The instalment calculator on the car page (2026-11-04). The client: "un
 * calculator simplu ... interesul 6.99% ... butoane 6 luni, un an, doi, 3, 4 și
 * 5 ... by default bifa la 5 ani pentru a arăta rata cea mai mică posibil".
 *
 * A French loan, as every bank in Spain quotes them: the same instalment every
 * month, cuota = C · i / (1 − (1 + i)^−n) with i = TIN / 12. No opening fee
 * here, so the TAE is only the TIN compounded monthly. The page says so, and that
 * the lender sets the real figures (Ley 16/2011 art. 9: any figure of the cost
 * of credit goes with the TAE, the TIN, the amount, the term and the total).
 *
 * The script on the car page does the same sums when the deposit changes;
 * CarFinanceTest holds both to the coches.net example.
 */
final class Finance
{
    /** Nominal interest a year, in %. */
    public const TIN = 6.99;

    /** The terms offered, in months. The last one is chosen when the page opens: its instalment is the lowest. */
    public const TERMS = [6, 12, 24, 36, 48, 60];

    /**
     * The lenders he works with, in the order he wrote them (WhatsApp,
     * 2026-11-01): key => [name, width ÷ height of the mark, weight]. The file
     * is public/img/banks/<key>.svg, its viewBox trimmed to the ink. The ratio
     * sets each mark's width so all of them cover the same area — a long
     * wordmark and a compact one then weigh the same, which one shared height
     * does not do. Weight corrects what area cannot see: BBVA's strokes are
     * twice as thick as Santander's, so at the same area it looked larger.
     */
    public const BANKS = [
        'bbva'      => ['BBVA', 3.34, .88],
        'lendrock'  => ['Lendrock', 8.47],
        'santander' => ['Santander', 5.74],
        'sabadell'  => ['Banco Sabadell', 5.16],
        'sofinco'   => ['Sofinco', 4.53],
        'cofidis'   => ['Cofidis', 4.46],
        'confia'    => ['Confía', 3.32],
        'ibancar'   => ['Ibancar', 4.75],
        'fintonic'  => ['Fintonic', 4.41],
    ];

    /** The width a mark is drawn at, in px: the same area (px²) for every one, times its weight. */
    public static function markWidth(float $ratio, float $weight = 1, int $area = 1600): int
    {
        return (int) round(sqrt($area * $ratio) * $weight);
    }

    /** The monthly instalment, to the cent. */
    public static function monthly(float $amount, int $months, float $tin = self::TIN): float
    {
        if ($amount <= 0 || $months <= 0) {
            return 0.0;
        }
        $i = $tin / 100 / 12;
        if ($i <= 0) {
            return round($amount / $months, 2);
        }

        return round($amount * $i / (1 - (1 + $i) ** -$months), 2);
    }

    /** The TAE with no fees: the TIN compounded monthly, in %, two decimals. */
    public static function tae(float $tin = self::TIN): float
    {
        return round(((1 + $tin / 100 / 12) ** 12 - 1) * 100, 2);
    }

    /**
     * One row per term for a car at this price, with nothing paid up front.
     *
     * @return list<array{months:int, label:string, amount:int, monthly:float, total:float}>
     */
    public static function plans(int $price, int $down = 0): array
    {
        $amount = max(0, $price - max(0, $down));
        $out = [];
        foreach (self::TERMS as $n) {
            $q = self::monthly($amount, $n);
            $out[] = ['months' => $n, 'label' => self::term($n), 'amount' => $amount, 'monthly' => $q, 'total' => round($q * $n, 2)];
        }

        return $out;
    }

    /** "6 meses", "1 año", "5 años". */
    public static function term(int $months): string
    {
        if ($months % 12) {
            return $months . ' meses';
        }
        $y = intdiv($months, 12);

        return $y === 1 ? '1 año' : $y . ' años';
    }

    /** 490.94 → "490,94 €"; whole euros with $cents = false. */
    public static function money(float $n, bool $cents = true): string
    {
        return number_format($n, $cents ? 2 : 0, ',', '.') . ' €';
    }

    /** 7.22 → "7,22 %". */
    public static function pct(float $n): string
    {
        return number_format($n, 2, ',', '.') . ' %';
    }
}
