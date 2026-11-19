<?php

namespace App\Http\Controllers;

use App\Models\ReviewLink;
use App\Models\Testimonial;
use App\Support\CarPhotos;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * /opinion/{token}: a customer writes their own opinion through the link the
 * owner sent them (App\Models\ReviewLink — read its docblock).
 *
 * Four states of one page: the form; "gracias" right after sending (kept in the
 * session, so a refresh still thanks them); "ya me llegó" when the link has been
 * used; "cerrado" when it expired or he closed it.
 *
 * What is sent becomes a Testimonial. With a photograph it is published at once
 * and opens the home page's row (the client, 2026-11-19: "când cineva lasă
 * review cu poză, acest review să apară la reviews"); he can still hide or edit
 * it in the panel. Without one it waits there hidden: the row shows only
 * reviews with a photograph, and he adds one or publishes it as it is.
 *
 * The photograph can be any picture a phone or a computer keeps: JPEG, PNG,
 * WebP, GIF, BMP, and Apple's HEIC, AVIF and TIFF, which are converted first
 * (the client, 2026-11-19: "orice tip de imagine ... formatele de la Apple").
 * It is opened, turned the way the phone held it, made at most 2400 px on its
 * long side and saved again as a new JPEG. That drops every metadata field the
 * phone wrote, the GPS position of the customer's home among them, before the
 * file is ever served. The page makes it small before it goes up when the
 * browser can draw it; this does the same work for what it cannot.
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
            'live'  => $sent && $link->testimonial?->is_active,
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
            'photo'   => ['nullable', 'file', 'max:' . self::MAX_PHOTO_KB, function ($attribute, $value, $fail) {
                if ($value instanceof UploadedFile && !self::isPicture($value)) {
                    $fail('Ese archivo no es una foto. Prueba con otra.');
                }
            }],
            'consent' => 'accepted',
        ], [
            'name.required'    => 'Escribe tu nombre, como quieres que salga.',
            'quote.required'   => 'Escribe tu opinión.',
            'quote.min'        => 'Un poco más, por favor: al menos 20 letras.',
            'quote.max'        => 'Como mucho 1.000 letras.',
            'photo.max'        => 'La foto pesa demasiado: como mucho 12 MB.',
            'photo.uploaded'   => 'La foto no ha llegado entera. Prueba otra vez.',
            'consent.accepted' => 'Marca la casilla para que pueda publicarla.',
        ]);

        $photo = $request->hasFile('photo') ? self::keep($request->file('photo')) : null;
        if ($request->hasFile('photo') && !$photo) {
            return back()->withInput()->withErrors(['photo' => 'No consigo abrir esa foto. Prueba con otra.']);
        }

        $t = Testimonial::create([
            'author_name' => trim(preg_replace('/\s+/u', ' ', self::plain($data['name']))),
            'quote'       => trim(self::plain($data['quote'])),
            'image_path'  => $photo,
            'is_active'   => $photo !== null,   // see the class docblock
            'order_index' => 0,
        ]);
        $link->update(['used_at' => now(), 'testimonial_id' => $t->id]);
        // the row reads its colour and its crop from the 320px copy: made now,
        // so the first visitor sees the card it should be, not the plain navy
        if ($photo) {
            \App\Http\Controllers\Admin\SheetController::derivative($photo, 320);
        }
        $request->session()->put('opinion_sent', $token);

        return redirect()->route('opinion', $token);
    }

    /**
     * The text without markup. Not strip_tags: it reads "Muy bien <3 gracias"
     * as a tag and drops everything after the "<". Only what is a tag goes;
     * every page prints the text escaped anyway.
     */
    private static function plain(string $s): string
    {
        return preg_replace('/<\/?[a-z][a-z0-9]*(?:\s[^<>]*)?\/?>|<!--.*?-->/is', '', $s);
    }

    private static function find(string $token): ReviewLink
    {
        abort_unless(ReviewLink::ready(), 404);

        return ReviewLink::where('token', $token)->firstOrFail();
    }

    /** HEIC, HEIF and AVIF: one container, read by heif-convert. */
    private static function isHeif(UploadedFile $file): bool
    {
        return in_array((string) $file->getMimeType(), ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence', 'image/avif'], true)
            || (bool) preg_match('/\.(heic|heif|avif)$/i', (string) $file->getClientOriginalName());
    }

    /** a picture by what the file is, not what it is called; never an SVG (a document) */
    private static function isPicture(UploadedFile $file): bool
    {
        $mime = (string) $file->getMimeType();

        return self::isHeif($file) || (str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml');
    }

    /** The photograph, re-drawn: upright, ≤ 2400 px, a fresh JPEG with no metadata. */
    private static function keep(UploadedFile $file): ?string
    {
        $src = $file->getRealPath();
        $made = null;   // the JPEG a conversion wrote, removed at the end
        try {
            // libheif and ffmpeg apply the photo's own turn while converting:
            // what they write is upright, and is not turned again below
            if (self::isHeif($file)) {
                $src = $made = CarPhotos::heicToJpeg($src);
            } elseif ($file->getMimeType() === 'image/tiff') {
                $src = $made = self::tiffToJpeg($src);
            }

            return $src ? self::redraw($src, $made === null) : null;
        } finally {
            if ($made) {
                @unlink($made);
            }
        }
    }

    /**
     * A TIFF (a scanner, a computer), which GD cannot open: ffmpeg's TIFF decoder
     * and nothing else of ffmpeg's — the demuxer, the decoder and the protocol
     * are named, so a crafted file cannot make it read anything but itself.
     */
    private static function tiffToJpeg(string $src): ?string
    {
        if (!is_executable('/usr/bin/ffmpeg')) {
            return null;
        }
        $base = tempnam(sys_get_temp_dir(), 'tif');
        @unlink($base);
        $in = $base . '.tiff';
        $out = $base . '.jpg';
        copy($src, $in);
        try {
            $r = Process::timeout(60)->run(['/usr/bin/ffmpeg', '-nostdin', '-loglevel', 'error', '-protocol_whitelist', 'file',
                '-f', 'image2', '-c:v', 'tiff', '-i', $in, '-frames:v', '1', '-q:v', '2', '-y', $out]);
        } catch (\Throwable) {
            return null;
        } finally {
            @unlink($in);
        }

        return $r->successful() && is_file($out) && filesize($out) > 0 ? $out : null;
    }

    /** $exif: the file is the phone's own JPEG, whose EXIF says how it was held */
    private static function redraw(string $src, bool $exif): ?string
    {
        $info = @getimagesize($src);
        if (!$info || $info[0] < 200 || $info[1] < 200) {
            return null;
        }
        $im = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => @imagecreatefrompng($src),
            IMAGETYPE_WEBP => @imagecreatefromwebp($src),
            IMAGETYPE_GIF  => @imagecreatefromgif($src),
            IMAGETYPE_BMP  => @imagecreatefrombmp($src),
            default        => false,
        };
        if (!$im) {
            return null;
        }

        if ($exif && $info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
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
