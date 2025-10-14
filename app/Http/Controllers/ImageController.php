<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    public function resize(Request $request, int $w)
    {
        $path = (string) $request->query('p', '');
        if ($w < 80 || $w > 2400 || $path === '') {
            return response('Bad request', 400);
        }

        // Accept only local storage files: /storage/...
        $parsed = parse_url($path, PHP_URL_PATH) ?: '';
        if (strpos($parsed, '/storage/') !== 0) {
            return redirect()->away($path);
        }

        $relative = ltrim(substr($parsed, strlen('/storage/')), '/');
        $sourceFsPath = storage_path('app/public/' . $relative);
        if (!is_file($sourceFsPath)) {
            return response('Not found', 404);
        }

        $cacheDir = storage_path('app/public/cache');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $hash = md5($w . '|' . $relative . '|' . filemtime($sourceFsPath));
        $canWebp = function_exists('imagewebp');
        $ext = $canWebp ? 'webp' : 'jpg';
        $cachePath = $cacheDir . '/img_' . $hash . '.' . $ext;

        if (!is_file($cachePath)) {
            $img = $this->createImageFromFile($sourceFsPath);
            if (!$img) {
                return response('Unsupported', 415);
            }
            $srcW = imagesx($img);
            $srcH = imagesy($img);
            $ratio = min($w / $srcW, 1.0);
            $targetW = (int) max(1, round($srcW * $ratio));
            $targetH = (int) max(1, round($srcH * $ratio));
            $dst = imagecreatetruecolor($targetW, $targetH);
            imagecopyresampled($dst, $img, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
            if ($canWebp) {
                imagewebp($dst, $cachePath, 82);
            } else {
                imagejpeg($dst, $cachePath, 82);
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


