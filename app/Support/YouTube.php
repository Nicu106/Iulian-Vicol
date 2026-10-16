<?php

namespace App\Support;

/**
 * One reading of a YouTube link, used by the admin (to validate and to show
 * the preview), and by the car page (to draw the poster and the player).
 *
 * The dealer pastes whatever the share sheet of the moment gives him. The 15
 * links in the table are all `youtu.be/ID?si=…` or `?is=…`, but the same video
 * also arrives as watch?v=, m.youtube.com, /shorts/, /embed/ and /live/, with or
 * without a timestamp and with or without the scheme. All of them are the same
 * eleven characters; everything else is decoration, tracking, or the start time.
 *
 * The old car page (pages/vehicle.blade.php) did this inline. It accepted any
 * host — a Vimeo link came out as a YouTube embed of a nonsense id — and fell
 * back to the raw URL as an iframe src. Here a link is either a YouTube video,
 * with an id that has the shape of one, or it is nothing.
 */
final class YouTube
{
    private const HOSTS = [
        'youtube.com', 'www.youtube.com', 'm.youtube.com', 'music.youtube.com',
        'youtu.be', 'www.youtu.be',
        'youtube-nocookie.com', 'www.youtube-nocookie.com',
    ];

    /** Path prefixes whose next segment is the id. */
    private const ID_AFTER = ['embed', 'shorts', 'live', 'v', 'e'];

    /**
     * ['id' => 11 chars, 'start' => seconds or null], or null if this is not a
     * link to one YouTube video.
     */
    public static function parse(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '' || mb_strlen($url) > 500) {
            return null;
        }
        // "youtu.be/abc…" typed by hand, without the scheme
        if (!preg_match('~^[a-z][a-z0-9+.-]*://~i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        $p = parse_url($url);
        if (!is_array($p) || empty($p['host'])) {
            return null;
        }
        $scheme = strtolower($p['scheme'] ?? '');
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }
        $host = strtolower($p['host']);
        if (!in_array($host, self::HOSTS, true)) {
            return null;
        }

        $seg = array_values(array_filter(explode('/', (string) ($p['path'] ?? '')), 'strlen'));
        parse_str((string) ($p['query'] ?? ''), $q);

        $id = null;
        if (str_ends_with($host, 'youtu.be')) {
            $id = $seg[0] ?? null;
        } elseif (isset($q['v']) && is_string($q['v'])) {
            $id = $q['v'];
        } elseif (isset($seg[0], $seg[1]) && in_array($seg[0], self::ID_AFTER, true)) {
            $id = $seg[1];
        }
        if (!is_string($id) || !preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) {
            return null;
        }

        $t = $q['t'] ?? $q['start'] ?? null;
        if ($t === null && !empty($p['fragment']) && preg_match('/(?:^|&)t=([^&]+)/', $p['fragment'], $m)) {
            $t = $m[1];
        }

        return ['id' => $id, 'start' => self::seconds($t)];
    }

    /** True when the link is one YouTube video. */
    public static function valid(?string $url): bool
    {
        return self::parse($url) !== null;
    }

    /**
     * The form the database keeps: the watch URL, the start time if there was
     * one, and nothing else — the `si`/`is` share-tracking parameters go.
     */
    public static function canonical(?string $url): ?string
    {
        $v = self::parse($url);
        if (!$v) {
            return null;
        }

        return 'https://www.youtube.com/watch?v=' . $v['id'] . ($v['start'] ? '&t=' . $v['start'] . 's' : '');
    }

    /**
     * The player, from the privacy-enhanced domain (the CSP's frame-src lists
     * it). Only ever loaded after a press — nothing from YouTube runs before.
     */
    public static function embed(array $v, bool $autoplay = true): string
    {
        return 'https://www.youtube-nocookie.com/embed/' . $v['id'] . '?' . http_build_query(array_filter([
            'autoplay'    => $autoplay ? 1 : null,
            'rel'         => 0,
            'playsinline' => 1,
            'start'       => $v['start'] ?: null,
        ], fn ($x) => $x !== null));   // not a bare array_filter: it would drop rel=0
    }

    /**
     * A still from i.ytimg.com. maxresdefault (1280x720) exists only for HD
     * uploads and answers with a 120x90 grey placeholder otherwise, so the page
     * script checks the width it got and steps down to hqdefault (480x360, which
     * always exists: the 16:9 frame letterboxed in 4:3, which object-fit: cover
     * on a 16:9 box crops back to exactly the frame). mqdefault is 320x180, a
     * true 16:9 — the admin's small preview.
     */
    public static function poster(string $id, string $size = 'maxresdefault'): string
    {
        return 'https://i.ytimg.com/vi/' . $id . '/' . $size . '.jpg';
    }

    /**
     * The best still that actually exists, for the car page's poster.
     *
     * Measured over the 15 videos in the table (2026-10-16): 12 have
     * maxresdefault, 3 do not — and for those the URL answers 404 with a 120x90
     * grey placeholder, which a browser happily shows. sddefault (640x480) was
     * there for all 15. So the server asks once per video (HEAD, 2 s), keeps the
     * answer for 30 days, and the page names a still that exists — without
     * JavaScript too, and without a grey frame being swapped for a picture.
     * A network failure falls back to hqdefault, which always exists, and is
     * remembered for an hour only.
     */
    public static function still(string $id): string
    {
        if (!preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) {
            return self::poster($id, 'hqdefault');
        }
        $key = 'yt-still:' . $id;
        $hit = \Illuminate\Support\Facades\Cache::get($key);
        if (is_string($hit)) {
            return self::poster($id, $hit);
        }
        $size = 'hqdefault';
        $ttl = 3600;
        try {
            foreach (['maxresdefault', 'sddefault'] as $s) {
                $r = \Illuminate\Support\Facades\Http::connectTimeout(2)->timeout(2)->head(self::poster($id, $s));
                if ($r->successful()) {
                    $size = $s;
                    break;
                }
            }
            $ttl = 30 * 86400;
        } catch (\Throwable) {
            // offline or slow: the still that always exists, and ask again later
        }
        \Illuminate\Support\Facades\Cache::put($key, $size, $ttl);

        return self::poster($id, $size);
    }

    /** The page on YouTube itself, for anyone without JavaScript. */
    public static function watch(array $v): string
    {
        return 'https://www.youtube.com/watch?v=' . $v['id'] . ($v['start'] ? '&t=' . $v['start'] . 's' : '');
    }

    /** "90", "90s", "1m30s", "1h2m3s" → seconds; anything else → null. */
    private static function seconds(mixed $t): ?int
    {
        if (!is_string($t) && !is_int($t)) {
            return null;
        }
        $t = strtolower(trim((string) $t));
        if ($t === '') {
            return null;
        }
        if (preg_match('/^(\d+)s?$/', $t, $m)) {
            $n = (int) $m[1];
        } elseif (preg_match('/^(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?$/', $t, $m)) {
            $n = (int) ($m[1] ?? 0) * 3600 + (int) ($m[2] ?? 0) * 60 + (int) ($m[3] ?? 0);
        } else {
            return null;
        }

        return $n > 0 && $n < 86400 ? $n : null;
    }
}
