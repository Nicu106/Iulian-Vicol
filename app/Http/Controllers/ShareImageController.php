<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Support\Facades\File;

/**
 * /og/{slug} — the picture WhatsApp, Facebook and the rest show when a car's link
 * is pasted into a chat.
 *
 * A JPEG, 1200×630, cropped from the car's cover photograph. JPEG and not the
 * site's WebP derivatives because link-preview fetchers do not all read WebP, and
 * this image exists only for them. Built once per cover (the file name carries the
 * cover's mtime) and then served from disk. No extension in the URL: nginx answers
 * *.jpg itself and would never reach this.
 */
class ShareImageController extends Controller
{
    private const W = 1200;
    private const H = 630;

    public function show(string $slug)
    {
        $car = Vehicle::where('slug', $slug)->where('status', '!=', 'pending')->firstOrFail();
        $src = self::fsPath($car->cover_image) ?? public_path('img/banner/panamera.jpg');

        $dir = storage_path('app/public/cache/og');
        $out = $dir . '/' . $car->slug . '-' . filemtime($src) . '.jpg';

        if (!is_file($out)) {
            File::ensureDirectoryExists($dir);
            foreach (glob($dir . '/' . $car->slug . '-*.jpg') ?: [] as $old) {
                @unlink($old);   // an older cover's picture
            }
            if (!self::build($src, $out)) {
                abort(404);
            }
        }

        return response()->file($out, [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    /** The cover's file on disk, or null. Only our own storage. */
    public static function fsPath(?string $path): ?string
    {
        $p = parse_url((string) $path, PHP_URL_PATH) ?: '';
        if (!str_starts_with($p, '/storage/') || str_contains($p, '..')) {
            return null;
        }
        $fs = storage_path('app/public/' . substr($p, 9));
        return is_file($fs) ? $fs : null;
    }

    private static function build(string $src, string $out): bool
    {
        $info = @getimagesize($src);
        if (!$info) {
            return false;
        }
        $im = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => @imagecreatefrompng($src),
            IMAGETYPE_WEBP => @imagecreatefromwebp($src),
            default        => false,
        };
        if (!$im) {
            return false;
        }

        // phone photographs carry their rotation in EXIF
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $o = (int) (@exif_read_data($src)['Orientation'] ?? 1);
            $deg = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($deg) {
                $im = imagerotate($im, $deg, 0);
            }
        }

        // cover-crop to 1200×630, centred, a little above the middle so the roof stays in
        $w = imagesx($im); $h = imagesy($im);
        $scale = max(self::W / $w, self::H / $h);
        $cw = (int) round(self::W / $scale); $ch = (int) round(self::H / $scale);
        $cx = (int) round(($w - $cw) / 2);
        $cy = (int) round(min(max(($h - $ch) * 0.42, 0), $h - $ch));

        $dst = imagecreatetruecolor(self::W, self::H);
        imagecopyresampled($dst, $im, 0, 0, $cx, $cy, self::W, self::H, $cw, $ch);
        imageinterlace($dst, true);
        $ok = imagejpeg($dst, $out . '.tmp', 82) && rename($out . '.tmp', $out);
        imagedestroy($im); imagedestroy($dst);

        return $ok;
    }
}
