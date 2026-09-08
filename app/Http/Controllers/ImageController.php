<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    /**
     * WebP quality lives in App\Support\Img so the templates and this endpoint
     * cannot disagree about which file a URL names.
     * GD has no AVIF on this server (gd_info reports AVIF Support: no)
     * and there is no Imagick and no cwebp/avifenc binary, so WebP is the format
     * available without a system change. Measured on the heaviest file in the
     * library — 4.68 MB, 5712x4284 — this lands 27 KB at 400px, 110 KB at 800 and
     * 338 KB at 1600.
     */

    /** Widths a page may ask for. Anything else is rounded up to one of these, so
     *  a typo in a template cannot spawn a permanent derivative on disk beside the
     *  5.4 GB of originals. Mirrors App\Support\Img::WIDTHS. */
    private const WIDTHS = [320, 480, 720, 1080, 1600, 2000];

    public function resize(Request $request, int $w)
    {
        $path = (string) $request->query('p', '');
        if ($path === '') {
            return response('Bad request', 400);
        }
        if (!in_array($w, self::WIDTHS, true)) {
            $w = null;
            foreach (self::WIDTHS as $c) {
                if ($c >= (int) $request->route('w')) { $w = $c; break; }
            }
            $w = $w ?? (int) max(self::WIDTHS);
        }

        // Two roots, and only these two: the dealer's uploads under /storage/, and
        // the page banners that ship with the repo. The banners were served as raw
        // files straight out of public/ — contacto.jpg alone is 1.06 MB of JPEG on
        // a 390px phone — because this endpoint used to bounce anything that was
        // not /storage/ back to itself with a redirect.
        $parsed = parse_url($path, PHP_URL_PATH) ?: '';
        if (strpos($parsed, '/storage/') === 0) {
            $relative     = ltrim(substr($parsed, strlen('/storage/')), '/');
            $sourceFsPath = storage_path('app/public/' . $relative);
        } elseif (strpos($parsed, '/img/banner/') === 0) {
            $relative     = ltrim($parsed, '/');
            $sourceFsPath = public_path($relative);
        } else {
            // 404, never a redirect. This used to `redirect()->away($path)`, which
            // made GET /img/720?p=https://anywhere a working open redirect off
            // ivmotorclass.com — catalogued as SEC-04 in the vault and still live
            // here, because /img is on the BrandbookOnly allowlist and so bypasses
            // the lockdown that hides everything else. Verified before the fix:
            // 302 -> https://example.com/x.jpg.
            return response('Not found', 404);
        }

        // realpath before the is_file check: '..' inside p must not walk out of the
        // two roots above, and a symlink must not either.
        $real = realpath($sourceFsPath);
        $roots = [realpath(storage_path('app/public')), realpath(public_path('img/banner'))];
        $inside = false;
        foreach (array_filter($roots) as $root) {
            if ($real !== false && strpos($real, $root . DIRECTORY_SEPARATOR) === 0) {
                $inside = true;
                break;
            }
        }
        if (!$inside || !is_file($real)) {
            return response('Not found', 404);
        }
        $sourceFsPath = $real;

        $cacheDir = storage_path('app/public/cache');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        // 'v2' rompe la cache anterior: los thumbnails ya generados salieron girados
        // La calidad entra en la clave: cambiarla sin esto deja servidos para
        // siempre los derivados hechos con la anterior.
        $q = \App\Support\Img::QUALITY;
        $hash = \App\Support\Img::cacheKey($relative, $w, (int) filemtime($sourceFsPath));
        $canWebp = function_exists('imagewebp');
        $ext = $canWebp ? 'webp' : 'jpg';
        $cachePath = $cacheDir . '/img_' . $hash . '.' . $ext;

        if (!is_file($cachePath)) {
            $img = $this->createImageFromFile($sourceFsPath);
            if (!$img) {
                return response('Unsupported', 415);
            }
            // GD no aplica la etiqueta EXIF; los navegadores sí. Sin esto, el 9,8% de
            // las fotos (182 de 1.859) se sirven giradas y con la proporción transpuesta.
            $img = $this->applyExifOrientation($img, $sourceFsPath);
            $srcW = imagesx($img);   // ya orientada
            $srcH = imagesy($img);
            $ratio = min($w / $srcW, 1.0);
            $targetW = (int) max(1, round($srcW * $ratio));
            $targetH = (int) max(1, round($srcH * $ratio));
            $dst = imagecreatetruecolor($targetW, $targetH);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $img, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
            if ($canWebp) {
                imagewebp($dst, $cachePath, $q);
            } else {
                imagejpeg($dst, $cachePath, $q);
            }
            imagedestroy($dst);
            imagedestroy($img);
        }

        $mime = $canWebp ? 'image/webp' : 'image/jpeg';
        return response()->file($cachePath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable'
        ]);
    }


    /** Aplica la orientación EXIF, que GD ignora al decodificar. */
    private function applyExifOrientation($img, string $path)
    {
        if (!function_exists('exif_read_data')) {
            return $img;
        }

        $orientation = 1;
        try {
            $exif = @exif_read_data($path);
            $orientation = (int) ($exif['Orientation'] ?? 1);
        } catch (\Throwable $e) {
            return $img;
        }

        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
        if ($angle === 0) {
            return $img;
        }

        $rotated = @imagerotate($img, $angle, 0);
        if (!$rotated) {
            return $img;
        }
        imagedestroy($img);

        return $rotated;
    }

    private function createImageFromFile(string $path)
    {
        $info = getimagesize($path);
        if (!$info) return null;
        switch ($info['mime']) {
            case 'image/jpeg': return imagecreatefromjpeg($path);
            case 'image/png': return imagecreatefrompng($path);
            case 'image/webp': return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null;
            default: return null;
        }
    }
}


