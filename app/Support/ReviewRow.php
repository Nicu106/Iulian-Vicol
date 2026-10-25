<?php

namespace App\Support;

use App\Models\Testimonial;

/**
 * The home page's reviews (#reviews): the row of pairs chosen in the showroom
 * as 4.6 "La fila" (tag opiniones-v4.6) and brought here as a site component:
 * partials/reviews.blade.php, public/css/reviews.css, public/js/reviews.js.
 *
 * Everything a pair needs, prepared once so the view only lays it out:
 *  - every active review with a photograph and more than 20 characters of
 *    text (the home page's old floor: one review is a comma), NEWEST FIRST;
 *  - the text tidied for print, never altered (paragraphs(): CRLF paragraphs,
 *    runs of spaces, a space before a comma, an outer pair of quotation marks)
 *    and the name tidied the same way ("Diego&Paula" -> "Diego & Paula" with a
 *    no-break space; "MIguel" -> "Miguel"). The database row is never touched.
 *    author_location is never printed (CLAUDE.md §8);
 *  - the photograph's real shape (EXIF-aware, Img::size) and how it sits in the
 *    pair's 3:4 window (look()): it COVERS the window when that trims 10 % or
 *    less of one side, the trim placed where the picture is busiest (edges and
 *    skin: the people and the car); otherwise it is shown WHOLE, at even
 *    margins, over a blurred copy of itself (ambient());
 *  - the card's colour: the brand's --mc-navy with 12 % of the photograph's own
 *    hue mixed in (OKLab). A whisper, never a field (the client: the brandbook's
 *    colours only); a picture with almost no colour leaves the navy as it is.
 *
 * Measured once per photograph from its 320px derivative and cached by path +
 * mtime (storage/app/reviews, storage/app/public/reviews/amb), so a new or
 * replaced photograph is measured on its first view and nothing else ever is.
 * Nobody has to run anything.
 */
final class ReviewRow
{
    /** the pair's photo window, width / height */
    public const FRAME = 0.75;
    /** cover the window when that trims at most this much of one side */
    public const COVER = 0.10;
    /** --mc-navy (mc-tokens.css), and how much of the photo's hue it takes */
    public const NAVY = '#0E2E57';
    public const MIX = 0.12;

    /** The rows, newest first, ready to print. */
    public static function rows()
    {
        $list = Testimonial::where('is_active', true)
            ->whereNotNull('image_path')
            ->orderByDesc('created_at')->orderByDesc('id')
            ->get()
            ->filter(fn ($t) => mb_strlen(trim((string) $t->quote)) > 20)
            ->values();
        // every photograph's size in one cache read, not one per review
        Img::prime($list->pluck('image_path'));

        return $list
            ->map(function ($t) {
                $paras = self::paragraphs((string) $t->quote);
                $size  = Img::size($t->image_path) ?: [1200, 1600];
                $ratio = $size[1] > 0 ? $size[0] / $size[1] : self::FRAME;
                $look  = self::look($t->image_path, round($ratio, 4));
                $name  = self::name((string) $t->author_name);

                return (object) [
                    'id'    => (int) $t->id,
                    'name'  => $name,
                    'paras' => $paras,
                    'text'  => implode(' ', $paras),
                    'img'   => $t->image_path,
                    'w'     => (int) $size[0],
                    'h'     => (int) $size[1],
                    'ratio' => round($ratio, 4),
                    'card'  => $look['card'],
                    'fit'   => $look['fit'],
                    'fx'    => $look['fx'],
                    'fy'    => $look['fy'],
                    'amb'   => $look['fit'] === 'cover' ? null : self::ambient($t->image_path),
                ];
            });
    }

    /** What the script needs to open a review whole. */
    public static function json($rows): string
    {
        return json_encode($rows->map(fn ($r) => [
            'id'      => $r->id,
            'name'    => $r->name,
            'caption' => $r->name . ', con su coche',
            'paras'   => $r->paras,
            'w'       => $r->w,
            'h'       => $r->h,
            'src'     => Img::url($r->img, 1080) ?? $r->img,
            'srcset'  => Img::srcset($r->img, 1600),
        ])->values(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /** ['card' => #hex, 'fit' => cover|tall|wide, 'fx' => %, 'fy' => %, ...] */
    public static function look(string $img, float $ratio): array
    {
        $mt = (int) @filemtime(public_path(ltrim($img, '/')));
        $file = storage_path('app/reviews/' . substr(md5('rv1|' . $img . '|' . $mt . '|' . $ratio), 0, 16) . '.json');
        if (is_file($file) && ($k = json_decode((string) @file_get_contents($file), true))) {
            return $k;
        }
        $h = 255.0; $c = 0.0; $fx = 50.0; $fy = 50.0;
        $crop = $ratio < self::FRAME ? 1 - $ratio / self::FRAME : 1 - self::FRAME / $ratio;
        $fit = $crop <= self::COVER ? 'cover' : ($ratio < self::FRAME ? 'tall' : 'wide');
        $small = Img::url($img, 320);
        $path = $small ? storage_path('app/public/cache/' . basename((string) parse_url($small, PHP_URL_PATH))) : null;
        if ($path && is_file($path) && ($im = @imagecreatefromwebp($path))) {
            $w = imagesx($im); $hh = imagesy($im);
            $tw = 64; $th = max(3, (int) round($hh * $tw / max(1, $w)));
            $sm = imagecreatetruecolor($tw, $th);
            imagecopyresampled($sm, $im, 0, 0, 0, 0, $tw, $th, $w, $hh);
            $sa = $sb = $sw = 0.0; $px = [];
            for ($y = 0; $y < $th; $y++) {
                for ($x = 0; $x < $tw; $x++) {
                    $rgb = imagecolorat($sm, $x, $y);
                    $px[$y][$x] = [($rgb >> 16) & 255, ($rgb >> 8) & 255, $rgb & 255];
                    [, $A, $B] = self::oklab(...$px[$y][$x]);
                    $k = sqrt($A * $A + $B * $B);
                    $sa += $k * $A; $sb += $k * $B; $sw += $k;
                }
            }
            imagedestroy($im); imagedestroy($sm);
            if ($sw > 0) {
                $A = $sa / $sw; $B = $sb / $sw; $c = sqrt($A * $A + $B * $B);
                if ($c >= 0.008) { $h = fmod(rad2deg(atan2($B, $A)) + 360, 360); }
            }
            // the trim of a covering picture goes where it is busiest: edges, and skin x3
            if ($fit === 'cover' && $crop > 0.002) {
                $rows = array_fill(0, $th, 0.0); $cols = array_fill(0, $tw, 0.0);
                $l = fn ($q) => 0.3 * $q[0] + 0.59 * $q[1] + 0.11 * $q[2];
                for ($y = 1; $y < $th - 1; $y++) {
                    for ($x = 1; $x < $tw - 1; $x++) {
                        [$r, $g, $b] = $px[$y][$x];
                        $e = abs($l($px[$y][$x + 1]) - $l($px[$y][$x - 1])) + abs($l($px[$y + 1][$x]) - $l($px[$y - 1][$x]));
                        $skin = $r > 95 && $g > 40 && $b > 20 && max($r, $g, $b) - min($r, $g, $b) > 15 && abs($r - $g) > 15 && $r > $g && $r > $b;
                        $v = $e + ($skin ? 120 : 0);
                        $rows[$y] += $v; $cols[$x] += $v;
                    }
                }
                $best = function (array $line, float $keep) {
                    $n = count($line); $win = max(1, (int) round($n * $keep)); $span = $n - $win;
                    if ($span <= 0) return 50.0;
                    $sum = array_sum(array_slice($line, 0, $win)); $bs = $sum; $bo = 0;
                    for ($o = 1; $o <= $span; $o++) { $sum += $line[$o + $win - 1] - $line[$o - 1]; if ($sum > $bs) { $bs = $sum; $bo = $o; } }
                    return round($bo / $span * 100, 1);
                };
                if ($ratio < self::FRAME) { $fy = $best($rows, 1 - $crop); } else { $fx = $best($cols, 1 - $crop); }
            }
        }
        $card = self::NAVY;
        if ($c >= 0.008) {
            $n = self::oklab(hexdec(substr(self::NAVY, 1, 2)), hexdec(substr(self::NAVY, 3, 2)), hexdec(substr(self::NAVY, 5, 2)));
            $p = [.55, .12 * cos(deg2rad($h)), .12 * sin(deg2rad($h))];
            $card = self::hex($n[0] + ($p[0] - $n[0]) * self::MIX, $n[1] + ($p[1] - $n[1]) * self::MIX, $n[2] + ($p[2] - $n[2]) * self::MIX);
        }
        $out = ['card' => $card, 'hue' => round($h), 'chroma' => round($c, 4), 'fit' => $fit, 'crop' => round($crop, 3), 'fx' => $fx, 'fy' => $fy];
        @file_put_contents($file, json_encode($out));
        return $out;
    }

    /**
     * The photograph blurred far past recognition, for the mat behind a picture
     * shown whole: made once from the 320px derivative (28px, enlarged to 224,
     * ten blur passes) and served as a ~2 KB file, so nothing runs a CSS blur.
     */
    public static function ambient(string $img): ?string
    {
        $mt = (int) @filemtime(public_path(ltrim($img, '/')));
        $name = 'reviews/amb/' . substr(md5($img . '|' . $mt), 0, 16) . '.webp';
        $fs = storage_path('app/public/' . $name);
        if (is_file($fs)) {
            return '/storage/' . $name;
        }
        $small = Img::url($img, 320);
        if (!$small || !str_starts_with($small, '/storage/cache/')) {
            return null;
        }
        try {
            $im = @imagecreatefromwebp(storage_path('app/public/cache/' . basename((string) parse_url($small, PHP_URL_PATH))));
            if (!$im) {
                return null;
            }
            $w = imagesx($im); $h = imagesy($im);
            $tw = 28; $th = max(1, (int) round($h * $tw / max(1, $w)));
            $t = imagecreatetruecolor($tw, $th);
            imagecopyresampled($t, $im, 0, 0, 0, 0, $tw, $th, $w, $h);
            $big = imagescale($t, $tw * 8, $th * 8, IMG_BICUBIC);
            for ($k = 0; $k < 10; $k++) {
                imagefilter($big, IMG_FILTER_GAUSSIAN_BLUR);
            }
            @mkdir(dirname($fs), 0775, true);
            $ok = imagewebp($big, $fs, 70);
            imagedestroy($im); imagedestroy($t); imagedestroy($big);
            return $ok ? '/storage/' . $name : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Print-tidy paragraphs (as the showroom's). The database row is never touched. */
    public static function paragraphs(string $q): array
    {
        $q = trim(str_replace(["\r\n", "\r"], "\n", $q));
        // a review typed entirely inside quotation marks: the layout draws its own
        if (preg_match('/^["“«](.*)["”»]$/su', $q, $m) && !preg_match('/["“”«»]/u', $m[1])) {
            $q = trim($m[1]);
        }
        $out = [];
        foreach (preg_split('/\n\s*/u', $q) as $p) {
            $p = preg_replace('/[ \t\x{00A0}]+/u', ' ', trim($p));
            $p = preg_replace('/ +([,;:.!?])/u', '$1', $p);
            if ($p !== '') {
                $out[] = $p;
            }
        }
        return $out ?: [''];
    }

    /** "MIguel" -> "Miguel" (a held shift key); a no-break space before "&". */
    public static function name(string $n): string
    {
        $n = trim(preg_replace('/\s+/u', ' ', $n));
        $n = preg_replace_callback('/\b(\p{Lu})(\p{Lu})(?=\p{Ll})/u', fn ($m) => $m[1] . mb_strtolower($m[2]), $n);
        return preg_replace('/\s*&\s*/u', "\u{00A0}& ", $n);
    }

    private static function oklab(int $r, int $g, int $b): array
    {
        $f = fn ($v) => ($v /= 255) <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        [$r, $g, $b] = [$f($r), $f($g), $f($b)];
        $l = (0.4122214708 * $r + 0.5363325363 * $g + 0.0514459929 * $b) ** (1 / 3);
        $m = (0.2119034982 * $r + 0.6806995451 * $g + 0.1073969566 * $b) ** (1 / 3);
        $s = (0.0883024619 * $r + 0.2817188376 * $g + 0.6299787005 * $b) ** (1 / 3);
        return [0.2104542553 * $l + 0.7936177850 * $m - 0.0040720468 * $s,
                1.9779984951 * $l - 2.4285922050 * $m + 0.4505937099 * $s,
                0.0259040371 * $l + 0.7827717662 * $m - 0.8086757660 * $s];
    }

    /** OKLab to #RRGGBB, clamped into sRGB. */
    private static function hex(float $L, float $a, float $b): string
    {
        $l = ($L + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
        $m = ($L - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
        $s = ($L - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;
        $rgb = [4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s,
                -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s,
                -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s];
        return '#' . implode('', array_map(function ($v) {
            $v = max(0, min(1, $v));
            $v = $v <= 0.0031308 ? 12.92 * $v : 1.055 * $v ** (1 / 2.4) - 0.055;
            return sprintf('%02X', (int) round($v * 255));
        }, $rgb));
    }
}
