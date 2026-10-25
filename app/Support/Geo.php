<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use MaxMind\Db\Reader;

/**
 * The town an IP address is in, from a database kept on this server.
 *
 * Nothing leaves the server and the IP is not kept: App\Support\Stats asks once
 * per person and day and stores only the answer ("ES|Andalusia|Málaga|36.72|-4.42")
 * in its counters. The database is DB-IP's "IP to City Lite" (CC BY 4.0, credited
 * on the Estadísticas page), refreshed monthly by `php artisan stats:geo`.
 *
 * Accuracy, said plainly on the page: a home or office line usually gives the
 * right town; mobile data often gives the operator's city instead (Madrid, Málaga),
 * and iCloud Private Relay gives a town in the same region. Good for "where are my
 * visitors", not for "which street".
 */
final class Geo
{
    public const FILE = 'app/geo/dbip-city-lite.mmdb';

    /** Who runs the network an address belongs to (DB-IP "IP to ASN Lite"). */
    public const ASN_FILE = 'app/geo/dbip-asn-lite.mmdb';

    /**
     * Networks that hide where the person is: iCloud Private Relay goes out
     * through Akamai (36183), Cloudflare (13335) and Fastly (54113); Cloudflare
     * also carries WARP. Real people, in an unknown town — the database files
     * them under Algeria or Andorra, which is wrong. Counted, placed nowhere.
     */
    public const RELAY = [36183, 13335, 54113];

    /**
     * Server farms. A person does not browse a dealer's site from Amazon's or
     * Hetzner's machines; a robot posing as a browser does (Meta's and Google's
     * own crawlers among them, measured in the log 11–24.10). Not counted.
     */
    public const HOSTING = [
        16509, 14618, 8987,             // Amazon
        8075, 8068, 8069,               // Microsoft / Azure
        15169, 396982, 19527, 36040,    // Google, Google Cloud
        32934, 63293,                   // Meta
        24940, 213230, 212317,          // Hetzner
        16276, 35540,                   // OVH
        14061, 63949, 20473, 51167, 197540, 12876, 8560, 62240, 49981, 9009, 60068, 212238, 202425,
        45102, 37963, 132203, 45090, 31898, 136907, 55990, 21859, 46664, 53667, 40021,
    ];

    private static ?Reader $reader = null;

    private static bool $failed = false;

    private static ?Reader $asn = null;

    private static bool $asnFailed = false;

    /** @return array{cc:string,region:string,city:string,lat:float,lon:float}|null */
    public static function find(string $ip): ?array
    {
        if ($ip === '' || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }
        $net = self::network($ip);
        if ($net && in_array($net['asn'], self::RELAY, true)) {
            return ['cc' => 'ZZ', 'region' => '', 'city' => '', 'lat' => 0.0, 'lon' => 0.0];
        }
        $r = self::reader();
        if (! $r) {
            return null;
        }
        try {
            $x = $r->get($ip);
        } catch (\Throwable $e) {
            return null;
        }
        $cc = strtoupper((string) ($x['country']['iso_code'] ?? ''));
        if (! preg_match('~^[A-Z]{2}$~', $cc)) {
            return null;
        }
        $city = (string) ($x['city']['names']['es'] ?? $x['city']['names']['en'] ?? '');
        // "Madrid (Fuencarral - El Pardo)": the district is noise at this scale.
        $city = trim(preg_replace('~\s*\(.*\)\s*$~u', '', $city));
        $region = (string) ($x['subdivisions'][0]['names']['en'] ?? '');

        return [
            'cc'     => $cc,
            'region' => self::clean($region),
            'city'   => self::clean($city),
            'lat'    => round((float) ($x['location']['latitude'] ?? 0), 2),
            'lon'    => round((float) ($x['location']['longitude'] ?? 0), 2),
        ];
    }

    /** The counter key: "ES|Andalusia|Málaga|36.72|-4.42". */
    public static function key(array $g): string
    {
        return mb_substr(implode('|', [$g['cc'], $g['region'], $g['city'], $g['lat'], $g['lon']]), 0, 160);
    }

    /** @return array{cc:string,region:string,city:string,lat:float,lon:float}|null */
    public static function parse(string $key): ?array
    {
        $p = explode('|', $key);
        if (count($p) !== 5 || ! preg_match('~^[A-Z]{2}$~', $p[0])) {
            return null;
        }

        return ['cc' => $p[0], 'region' => $p[1], 'city' => $p[2], 'lat' => (float) $p[3], 'lon' => (float) $p[4]];
    }

    /** @return array{asn:int,org:string}|null */
    public static function network(string $ip): ?array
    {
        if ($ip === '' || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }
        if (! self::$asn && ! self::$asnFailed) {
            $file = storage_path(self::ASN_FILE);
            try {
                self::$asn = is_file($file) ? new Reader($file) : null;
            } catch (\Throwable $e) {
                Log::warning('geo asn: ' . $e->getMessage());
            }
            self::$asnFailed = ! self::$asn;
        }
        if (! self::$asn) {
            return null;
        }
        try {
            $x = self::$asn->get($ip);
        } catch (\Throwable $e) {
            return null;
        }

        return $x ? ['asn' => (int) ($x['autonomous_system_number'] ?? 0), 'org' => (string) ($x['autonomous_system_organization'] ?? '')] : null;
    }

    /** A server farm, not a person's line (HOSTING, or a network that calls itself one). */
    public static function hosting(string $ip): bool
    {
        $net = self::network($ip);
        if (! $net || in_array($net['asn'], self::RELAY, true)) {
            return false;
        }

        return in_array($net['asn'], self::HOSTING, true)
            || (bool) preg_match('~hosting|data ?cent(er|re)|colocation|\bvps\b|dedicated server|server ?farm|cloud (services|computing|infrastructure)~i', $net['org']);
    }

    /** "España", "Moldavia" — the country in Spanish. */
    public static function country(string $cc): string
    {
        if ($cc === 'ZZ') {
            return 'Ubicación oculta';
        }
        $name = class_exists(\Locale::class) ? \Locale::getDisplayRegion('-' . $cc, 'es') : '';

        return ($name !== '' && $name !== $cc) ? $name : $cc;
    }

    /** "Málaga" in Spain, "Chisinau, Moldavia" abroad: the line of a visit. */
    public static function place(?array $g): string
    {
        if (! $g) {
            return '';
        }
        if ($g['cc'] === 'ZZ') {
            return 'ubicación oculta';
        }
        $city = $g['city'] !== '' ? $g['city'] : ($g['region'] !== '' ? $g['region'] : '');
        if ($g['cc'] === 'ES') {
            return mb_substr($city ?: 'España', 0, 80);
        }

        return mb_substr(($city !== '' ? $city . ', ' : '') . self::country($g['cc']), 0, 80);
    }

    public static function path(): string
    {
        return storage_path(self::FILE);
    }

    private static function reader(): ?Reader
    {
        if (self::$reader || self::$failed) {
            return self::$reader;
        }
        $file = self::path();
        if (! is_file($file)) {
            self::$failed = true;

            return null;
        }
        try {
            self::$reader = new Reader($file);
        } catch (\Throwable $e) {
            self::$failed = true;
            Log::warning('geo: ' . $e->getMessage());
        }

        return self::$reader;
    }

    /** Forget the open file: after `stats:geo` replaced it, and in tests. */
    public static function reset(): void
    {
        self::$reader = self::$asn = null;
        self::$failed = self::$asnFailed = false;
    }

    private static function clean(string $s): string
    {
        return trim(str_replace(['|', "\n", "\r"], ' ', $s));
    }
}
