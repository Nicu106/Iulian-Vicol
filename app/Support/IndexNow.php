<?php

namespace App\Support;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IndexNow: tells Bing (and through it Copilot and ChatGPT's search), Yandex,
 * Seznam, Naver and the others that a page changed, the minute it changes —
 * instead of waiting for their crawler to come back. Google does not take part;
 * it reads the sitemap.
 *
 * The key is public by design: the engines fetch /{KEY}.txt from this host to
 * check the request is ours (public/{KEY}.txt holds it). One POST to
 * api.indexnow.org reaches all of them.
 *
 * A URL is sent at most once every 5 minutes (their guidance: do not resubmit
 * what has not changed), only from production, and never from a test.
 * App\Jobs\IndexNowPing sends it from the queue, so saving a car in the panel
 * never waits on the network.
 */
final class IndexNow
{
    public const KEY = '422402102ae08e7f5a1b5f63515b7e3e';
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    /** The pages that change with a car: its own, its marque's, the catalogue, the home page. */
    public static function forCar(Vehicle $car): array
    {
        $marque = Marques::for($car->brand);

        return array_values(array_filter([
            url('/coche/' . $car->slug),
            $marque ? url('/catalogo/' . $marque['key']) : null,
            url('/catalogo'),
            url('/'),
        ]));
    }

    /** @param list<string> $urls absolute URLs on this host */
    public static function send(array $urls): int
    {
        if (! app()->environment('production')) {
            return 0;
        }
        $host = parse_url(url('/'), PHP_URL_HOST);
        $urls = array_values(array_filter(array_unique($urls),
            fn ($u) => parse_url($u, PHP_URL_HOST) === $host && Cache::add('indexnow:' . sha1($u), 1, 300)));
        if (! $urls) {
            return 0;
        }

        $r = Http::timeout(15)->acceptJson()->post(self::ENDPOINT, [
            'host'        => $host,
            'key'         => self::KEY,
            'keyLocation' => url('/' . self::KEY . '.txt'),
            'urlList'     => $urls,
        ]);
        // 200 / 202 = taken; 403 key, 422 host mismatch, 429 too many
        if (! in_array($r->status(), [200, 202], true)) {
            Log::warning('IndexNow ' . $r->status() . ' for ' . count($urls) . ' URLs: ' . substr($r->body(), 0, 200));
        }

        return $r->status();
    }
}
