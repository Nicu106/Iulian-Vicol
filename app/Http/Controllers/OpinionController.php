<?php

namespace App\Http\Controllers;

use App\Models\ReviewLink;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * /opinion/{token}: a customer writes their own opinion through the link the
 * owner sent them (App\Models\ReviewLink — read its docblock).
 *
 * Four states of one page: the form; "gracias" right after sending (kept in the
 * session, so a refresh still thanks them); "ya me llegó" when the link has been
 * used; "cerrado" when it expired or he closed it. What is sent becomes a hidden
 * Testimonial: he reads it and publishes it from the panel.
 *
 * The photograph is opened, turned the way the phone held it, made at most
 * 2400 px on its long side and saved again as a new JPEG. That drops every
 * metadata field the phone wrote, the GPS position of the customer's home
 * among them, before the file is ever served.
 */
class OpinionController extends Controller
{
    public const MAX_PHOTO_KB = 12288;   // a phone original runs to 11 MB
    private const LONG_SIDE = 2400;

    public function show(Request $request, string $token)
    {
        $link = self::find($token);
        $sent = $request->session()->get('opinion_sent') === $token;

        return response()->view('pages.opinion', [
            'link'  => $link,
            'state' => $sent ? 'gracias' : ($link->used_at ? 'recibida' : ($link->isOpen() ? 'form' : 'cerrado')),
            'name'  => $sent ? $link->testimonial?->author_name : $link->name,
            'maxKb' => self::MAX_PHOTO_KB,
        ])->header('Cache-Control', 'no-store')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function store(Request $request, string $token)
    {
        $link = self::find($token);
        if (!$link->isOpen()) {
            return redirect()->route('opinion', $token);
        }

        $data = $request->validate([
            'name'    => 'required|string|max:80',
            'quote'   => 'required|string|min:20|max:1000',
            'photo'   => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp|max:' . self::MAX_PHOTO_KB,
            'consent' => 'accepted',
        ], [
            'name.required'    => 'Escribe tu nombre, como quieres que salga.',
            'quote.required'   => 'Escribe tu opinión.',
            'quote.min'        => 'Un poco más, por favor: al menos 20 letras.',
            'quote.max'        => 'Como mucho 1.000 letras.',
            'photo.mimetypes'  => 'La foto tiene que ser JPG, PNG o WebP.',
            'photo.max'        => 'La foto pesa demasiado: como mucho 12 MB.',
            'photo.uploaded'   => 'La foto no ha llegado entera. Prueba otra vez.',
            'consent.accepted' => 'Marca la casilla para que pueda publicarla.',
        ]);

        $photo = $request->hasFile('photo') ? self::keep($request->file('photo')) : null;
        if ($request->hasFile('photo') && !$photo) {
            return back()->withInput()->withErrors(['photo' => 'No consigo abrir esa foto. Prueba con otra.']);
        }

        $t = Testimonial::create([
            'author_name' => trim(preg_replace('/\s+/u', ' ', strip_tags($data['name']))),
            'quote'       => trim(strip_tags($data['quote'])),
            'image_path'  => $photo,
            'is_active'   => false,      // he publishes it from the panel
            'order_index' => 0,
        ]);
        $link->update(['used_at' => now(), 'testimonial_id' => $t->id]);
        $request->session()->put('opinion_sent', $token);

        return redirect()->route('opinion', $token);
    }

    private static function find(string $token): ReviewLink
    {
        abort_unless(ReviewLink::ready(), 404);

        return ReviewLink::where('token', $token)->firstOrFail();
    }

    /** The photograph, re-drawn: upright, ≤ 2400 px, a fresh JPEG with no metadata. */
    private static function keep(UploadedFile $file): ?string
    {
        $src = $file->getRealPath();
        $info = @getimagesize($src);
        if (!$info || $info[0] < 200 || $info[1] < 200) {
            return null;
        }
        $im = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => @imagecreatefrompng($src),
            IMAGETYPE_WEBP => @imagecreatefromwebp($src),
            default        => false,
        };
        if (!$im) {
            return null;
        }

        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $o = (int) (@exif_read_data($src)['Orientation'] ?? 1);
            $turn = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($turn) {
                $r = imagerotate($im, $turn, 0);
                imagedestroy($im);
                $im = $r;
            }
        }

        $w = imagesx($im);
        $h = imagesy($im);
        $k = min(1, self::LONG_SIDE / max($w, $h));
        $nw = (int) round($w * $k);
        $nh = (int) round($h * $k);
        // a white ground, so a transparent PNG does not turn black as a JPEG
        $out = imagecreatetruecolor($nw, $nh);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($im);

        $path = 'testimonials/' . Str::random(40) . '.jpg';
        ob_start();
        imagejpeg($out, null, 86);
        imagedestroy($out);
        Storage::disk('public')->put($path, ob_get_clean());

        return Storage::url($path);
    }
}
