<?php

namespace App\Support;

/**
 * One place that builds an image URL, and the widths every page picks from.
 *
 * Before this, /coche served the dealer's ORIGINALS: the stage photograph and the
 * thumbnail strip pointed straight at /storage/..., so a 390px phone downloaded
 * 7.17 MB to show a car — one file of 3.59 MB and another of 1.80 MB. The
 * resizing endpoint existed and was simply never called from that page.
 *
 * Two things this fixes beyond routing through the resizer:
 *
 * 1. A VERSION IN THE URL. The endpoint answers with `immutable, max-age=1y`,
 *    which is right, but the URL carried nothing that changes when the photograph
 *    does. Its cache key includes the file's mtime, so replacing a photo made a
 *    new derivative — under the same URL a browser had been told to keep for a
 *    year. The admin swaps a picture and returning visitors see the old one until
 *    2027. `v` is that mtime, so a replaced photo is a new URL.
 *
 * 2. ONE LADDER OF WIDTHS. Derivatives are generated on demand and cached
 *    forever, so every distinct width is a permanent file on disk. Letting each
 *    page invent its own widths multiplies 5.4 GB of originals by however many
 *    numbers someone typed. These six cover 320px to 4K at DPR 2 and nothing
 *    outside them is asked for.
 */
final class Img
{
    /** The only widths that exist. Chosen so each step is ~1.4x the last: below
     *  that the byte saving is under the cost of another cached derivative. */
    public const WIDTHS = [320, 480, 720, 1080, 1600, 2000];

    /** Quality and cache generation live here, and the controller reads them from
     *  here, so the name of a cached file is decided in exactly one place. */
    public const QUALITY = 82;
    public const GENERATION = 'v3';

    /**
     * Build one derivative URL.
     *
     * If the derivative already exists it returns the FILE, which nginx serves
     * without waking PHP. Measured: routing the page banners through the endpoint
     * cut /catalogo's hero from 253 KB to 48 KB and still pushed LCP from 532 ms
     * to 680, because a static file became a Laravel boot. Pointing at the cached
     * file keeps the bytes and gives the milliseconds back.
     *
     * If it does not exist yet, the endpoint URL is returned: that request builds
     * it, and every later render of the page links the file directly.
     */
    public static function url(?string $path, int $w): ?string
    {
        $path = self::clean($path);
        if ($path === null) {
            return null;
        }
        $w = self::nearestWidth($w);

        $cached = self::cachedUrl($path, $w);
        if ($cached !== null) {
            return $cached;
        }

        $url = '/img/' . $w . '?p=' . rawurlencode($path);
        $v = self::version($path);

        return $v ? $url . '&v=' . $v : $url;
    }

    /** The cache file's name. The controller uses this too — one rule, one place. */
    public static function cacheKey(string $relative, int $w, int $mtime): string
    {
        return md5(self::GENERATION . '|' . $w . '|' . self::QUALITY . '|' . $relative . '|' . $mtime);
    }

    /** The public URL of an already-built derivative, or null if it is not built. */
    private static function cachedUrl(string $path, int $w): ?string
    {
        $fs = self::fsPath($path);
        if ($fs === null) {
            return null;
        }
        $key = 'cachedurl|' . $w . '|' . $fs . '|' . @filemtime($fs);
        if (array_key_exists($key, self::$memo)) {
            return self::$memo[$key];
        }

        $relative = self::relativeFor($path);
        $ext  = function_exists('imagewebp') ? 'webp' : 'jpg';
        $name = 'img_' . self::cacheKey($relative, $w, (int) @filemtime($fs)) . '.' . $ext;

        return self::$memo[$key] = is_file(storage_path('app/public/cache/' . $name))
            ? '/storage/cache/' . $name
            : null;
    }

    /** The string the cache key is built from, matching the controller's. */
    public static function relativeFor(string $path): string
    {
        return str_starts_with($path, '/storage/')
            ? ltrim(substr($path, strlen('/storage/')), '/')
            : ltrim($path, '/');
    }

    /**
     * A srcset across the ladder, stopping at the source's own width: asking for
     * a 2000px derivative of a 1200px original writes a bigger file with no more
     * detail in it, and the endpoint never upscales anyway.
     */
    public static function srcset(?string $path, int $max = 2000): string
    {
        $path = self::clean($path);
        if ($path === null) {
            return '';
        }
        $native = self::width($path);
        $out = [];
        foreach (self::WIDTHS as $w) {
            if ($w > $max) {
                break;
            }
            $out[] = self::url($path, $w) . ' ' . $w . 'w';
            if ($native && $w >= $native) {
                break;      // one step past the native width, then stop
            }
        }

        return implode(', ', $out);
    }

    /** In-request memo. A car page renders 42 thumbnails and each one would
     *  otherwise cost a getimagesize() plus an exif_read_data() — 84 file reads
     *  to render one page, before a single byte of image is served. */
    private static array $memo = [];

    /** Intrinsic size, for width/height attributes — the cheapest fix for CLS. */
    public static function size(?string $path): ?array
    {
        $key = (string) $path;
        if (array_key_exists($key, self::$memo)) {
            return self::$memo[$key];
        }

        $fs = self::fsPath(self::clean($path));
        if ($fs === null) {
            return self::$memo[$key] = null;
        }
        // Across requests too: these files never change without their mtime
        // changing, and the answer is two integers.
        $ck = 'imgsize|' . md5($fs) . '|' . @filemtime($fs);
        $hit = \Illuminate\Support\Facades\Cache::get($ck);
        if (is_array($hit)) {
            return self::$memo[$key] = $hit;
        }

        $s = @getimagesize($fs);
        if (!$s) {
            return self::$memo[$key] = null;
        }
        // EXIF 6 and 8 mean the file is stored on its side; the browser turns it,
        // so the size the LAYOUT sees is transposed. 9.8% of this library is
        // affected (182 of 1,859 files) and getting it wrong here reserves the
        // wrong box and moves the page.
        $o = self::orientation($fs);
        $out = ($o === 6 || $o === 8) ? [$s[1], $s[0]] : [$s[0], $s[1]];
        \Illuminate\Support\Facades\Cache::forever($ck, $out);

        return self::$memo[$key] = $out;
    }

    public static function nearestWidth(int $w): int
    {
        foreach (self::WIDTHS as $c) {
            if ($c >= $w) {
                return $c;
            }
        }
        return (int) end(self::WIDTHS);
    }

    private static function width(?string $path): ?int
    {
        $s = self::size($path);
        return $s ? $s[0] : null;
    }

    private static function version(string $path): ?int
    {
        $fs = self::fsPath($path);
        return $fs ? @filemtime($fs) ?: null : null;
    }

    /** Normalise to a site-absolute path, or null if it is not ours to resize. */
    private static function clean(?string $path): ?string
    {
        if (!is_string($path) || $path === '') {
            return null;
        }
        $p = parse_url($path, PHP_URL_PATH) ?: '';
        if ($p === '') {
            return null;
        }
        if ($p[0] !== '/') {
            $p = '/' . $p;
        }
        return (str_starts_with($p, '/storage/') || str_starts_with($p, '/img/banner/')) ? $p : null;
    }

    private static function fsPath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }
        if (str_starts_with($path, '/storage/')) {
            $fs = storage_path('app/public/' . ltrim(substr($path, 9), '/'));
        } else {
            $fs = public_path(ltrim($path, '/'));
        }
        return is_file($fs) ? $fs : null;
    }

    private static function orientation(string $fs): int
    {
        if (!function_exists('exif_read_data')) {
            return 1;
        }
        try {
            return (int) (@exif_read_data($fs)['Orientation'] ?? 1);
        } catch (\Throwable) {
            return 1;
        }
    }
}
