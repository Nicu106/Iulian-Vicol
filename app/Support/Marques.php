<?php

namespace App\Support;

/**
 * The five marques the dealer works in, and each one's colour.
 *
 * One list for the catalogue rows and for the wall at the top of a car's page,
 * so a BMW is the same blue in both places and a click from the row into the car
 * never changes colour.
 *
 * Colours are the marques' own, checked against white text:
 * VW 16.13:1 · Audi 6.56:1 · BMW 5.94:1 · Mercedes 17.22:1 · Porsche 5.46:1.
 * Mercedes' petrol blue (#00ADEF) measures 2.55:1 and is unusable, so it carries
 * their corporate black instead.
 */
final class Marques
{
    /** Least to most exclusive. This order is the catalogue's argument. */
    public const ALL = [
        ['key' => 'volkswagen', 'name' => 'Volkswagen',    'colour' => '#022254', 'match' => ['volkswagen', 'vw']],
        ['key' => 'audi',       'name' => 'Audi',          'colour' => '#930016', 'match' => ['audi']],
        ['key' => 'bmw',        'name' => 'BMW',           'colour' => '#004086', 'match' => ['bmw']],
        ['key' => 'mercedes',   'name' => 'Mercedes-Benz', 'colour' => '#01172E', 'match' => ['mercedes', 'mercedes-benz', 'mercedes benz']],
        ['key' => 'porsche',    'name' => 'Porsche',       'colour' => '#C50007', 'match' => ['porsche']],
    ];

    /**
     * The marque a stored brand belongs to, or null for one he does not specialise
     * in. Brand names drift in the data — "Mercedes", "Mercedes-benz", "Bmw" are
     * all in there — so this matches a normalised needle, not the stored string.
     */
    public static function for(?string $brand): ?array
    {
        $needle = mb_strtolower(trim((string) $brand));
        foreach (self::ALL as $m) {
            if (in_array($needle, $m['match'], true)) {
                return $m;
            }
        }

        return null;
    }
}
