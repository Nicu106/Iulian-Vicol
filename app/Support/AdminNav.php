<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The panel's sections, in one place, and the rule for how many fit where.
 *
 * Adding a section is one line in items(). Nothing else has to be touched: the
 * rail, the phone bar, the "Más" sheet and the /admin/mas page all read this.
 *
 * THE RULE. On the desk every section is in the rail, always — a vertical list
 * has room for twenty, and past eight or so they are grouped under a heading
 * ('group'). On a phone the bar holds FIVE at most. That number is not taste:
 * Material's navigation bar and Apple's tab bar both specify three to five,
 * because below 390px a sixth tab leaves each one 65px, narrower than a thumb
 * lands and too narrow for a word. So:
 *
 *   up to 5 sections   all of them in the bar
 *   6 or more          the first 4 in the bar, and a fifth tab, "Más", that
 *                      opens a sheet listing the rest
 *
 * This is how iOS itself behaves when an app has more tabs than fit. The ORDER
 * of items() is therefore the priority: the most-used sections first, because
 * the first four are the ones a thumb reaches without a second press.
 *
 * 'tab' is a short label for the bar ("Mensajes" rather than "Mensajes de
 * contacto"). The rail and the sheet use 'label'. A bar label longer than about
 * nine characters does not fit 78px at the 13px floor; admin-nav.mjs measures it.
 */
final class AdminNav
{
    public const BAR_MAX = 5;

    private static ?array $fake = null;
    private static ?string $fakeCurrent = null;

    /** Replace the sections for a test render. Pass null to restore. */
    public static function fake(?array $items, ?string $current = null): void
    {
        self::$fake = $items;
        self::$fakeCurrent = $current;
    }

    public static function current(): ?string
    {
        return self::$fake !== null ? self::$fakeCurrent : optional(request()->route())->getName();
    }

    /**
     * @return list<array{route:?string,label:string,tab:string,href:string,match:string,count:?int,group:?string,on:bool}>
     */
    public static function items(): array
    {
        $raw = self::$fake ?? [
            ['route' => 'admin.home',               'label' => 'Panel'],
            ['route' => 'admin.vehicles.index',     'label' => 'Coches',    'match' => 'admin.vehicles.*'],
            ['route' => 'admin.contacts.index',     'label' => 'Mensajes',  'match' => 'admin.contacts.*', 'count' => Inbox::counts()['real'] ?: null],
            ['route' => 'admin.testimonials.index', 'label' => 'Opiniones', 'match' => 'admin.testimonials.*'],
            ['route' => 'admin.sell-cars.index',    'label' => 'Ventas',    'match' => 'admin.sell-cars.*'],
            ['route' => 'admin.referrals.index',    'label' => 'Recomendaciones', 'tab' => 'Referidos', 'match' => 'admin.referrals.*',
             'count' => \App\Models\ReferralReward::whereIn('status', \App\Models\ReferralReward::OPEN)->count() ?: null],
        ];

        $current = self::current();

        return array_map(function (array $i) use ($current) {
            $i += ['route' => null, 'count' => null, 'group' => null];
            $i['match'] ??= (string) $i['route'];
            $i['tab']   ??= $i['label'];
            $i['href']  ??= route($i['route']);
            $i['on']      = $current !== null && Str::is($i['match'], $current);

            return $i;
        }, $raw);
    }

    /**
     * Which sections go in the phone bar and which go behind "Más".
     *
     * @return array{0: list<array>, 1: list<array>}  [bar, overflow]
     */
    public static function split(array $items): array
    {
        if (count($items) <= self::BAR_MAX) {
            return [$items, []];
        }

        return [
            array_slice($items, 0, self::BAR_MAX - 1),
            array_slice($items, self::BAR_MAX - 1),
        ];
    }
}
