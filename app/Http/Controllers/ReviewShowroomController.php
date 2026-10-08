<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Support\Img;
use Illuminate\Http\Response;

/**
 * /muestras/opiniones — a private showroom: four ways of showing the customer
 * reviews, for the client to choose one. Not linked from anywhere, noindex,
 * not in the sitemap. /muestras/opiniones/{n} renders one proposal on its own,
 * which is what the showroom's frames load.
 *
 * (2026-10-08) The client turned down all ten of the first round. This is
 * the second: three ideas pushed far, plus the one he drew himself.
 *
 *  1 La entrega    a dark pinned sequence, one customer per screen
 *  2 El muro       every delivery photograph in one justified wall
 *  3 Sus palabras  the strongest phrase of a review, huge, line by line
 *  4 Pares         the client's sketch: photo and words as a layered pair
 *
 * Two of them are pinned scroll sequences (PINNED). A pinned sequence cannot
 * live in a frame that is as tall as its content (there is nothing to scroll
 * inside it), so the showroom gives those a frame of a screen's height that
 * scrolls on its own (?embed=2); the other two keep the content-height frame
 * (?embed=1).
 *
 * Every proposal reads the same live data the home page reads, prepared once
 * here so the views only decide layout:
 *
 *  - the text is the customer's, untouched in the database. Here it is only
 *    tidied for print: CRLF paragraphs become paragraphs, runs of spaces
 *    collapse, a space before a comma goes, and a review typed entirely inside
 *    quotation marks loses the outer pair (each layout draws its own).
 *  - the name: "Diego&Paula" prints "Diego & Paula" with a no-break space
 *    before the ampersand so a couple never splits across lines on "&";
 *    a slip of the shift key ("MIguel") is printed "Miguel". The record keeps
 *    what the owner typed. author_location is never printed: most rows say
 *    Santander, including people whose own words say Valencia or Valladolid.
 *  - the photograph's real shape (EXIF-aware, via Img::size) — the box takes
 *    it, so nothing is cropped (CLAUDE.md §5).
 *  - an excerpt cut at a sentence end (sentences()), for the layouts that show
 *    a review's opening and open the rest on demand.
 */
class ReviewShowroomController extends Controller
{
    /** number => [name, what it is, where it fits]. The shell prints these;
     *  nothing of it appears inside a proposal. */
    public const VARIANTS = [
        1 => ['La entrega', 'Un cliente cada vez, a pantalla completa: su foto entera sobre su propia luz, y sus palabras. Avanza con el scroll.',
              'Para /por-que-nosotros, en la misma noche que la historia, o como página de opiniones.'],
        2 => ['El muro', 'Todas las entregas juntas, en un muro de fotos enteras. Cualquiera se toca y pasa al frente con su opinión.',
              'Para la home o /por-que-nosotros: la cantidad de clientes reales, de un solo vistazo.'],
        3 => ['Sus palabras', 'La frase más fuerte de cada opinión, enorme, línea a línea. La foto acompaña; la opinión entera, a un toque.',
              'Para la home: convence en segundos, con las palabras exactas de los clientes.'],
        4 => ['Pares', 'Tu idea: foto y opinión en pareja, una sobre otra. Al tocar la opinión pasa delante; al tocar la foto se abre entera.',
              'Para la home: ligera, en fila, hecha para el pulgar.'],
    ];

    /** Pinned scroll sequences: shown in a screen-tall frame that scrolls inside. */
    public const PINNED = [1, 3];

    /** 1 · La entrega: the sequence, in this order. Chosen for the words (a
     *  story each, an opening that stands alone) and for the photograph (people
     *  and car both in it, light enough to carry a dark stage). A review that
     *  is deleted simply drops out. */
    public const ENTREGA = [22, 21, 7, 26, 15, 24, 18, 8];

    /** 3 · Sus palabras: review id => its strongest phrase. Each MUST be a
     *  verbatim substring of the review (checked in phrases(); a phrase that is
     *  no longer in the text — the owner edited it — drops out, it is never
     *  shown altered). */
    public const PHRASES = [
        21 => 'parecía recién salido de fábrica',
        7  => 'el coche estaba mejor en persona que en las fotos',
        15 => 'este coche está como nuevo',
        26 => 'Incluso vino a recogernos al aeropuerto',
        25 => 'nuestra intención es conservar este coche otros 20 años',
        22 => 'hemos cruzado España para ir de vacaciones sin ningún problema',
        28 => 'va finísimo en carretera',
        20 => 'una compra de 10 en todos los sentidos',
    ];

    public function index(): Response
    {
        return response()
            ->view('muestras.opiniones', [
                'variants' => self::VARIANTS,
                'pinned'   => self::PINNED,
                'count'    => $this->reviews()->count(),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function show(int $n): Response
    {
        abort_unless(isset(self::VARIANTS[$n]), 404);
        $reviews = $this->reviews();
        $embed   = (int) request()->query('embed', 0);

        $data = [
            'n'       => $n,
            // inside the showroom: no repeated heading. 1 = the frame follows
            // the content's height; 2 = a screen-tall frame that scrolls itself
            'embed'   => $embed > 0,
            'framed'  => $embed === 2,
            'variant' => self::VARIANTS[$n],
            'reviews' => $reviews,
            'json'    => $this->json($reviews),
        ];
        if ($n === 1) {
            $data['beats'] = $this->pick($reviews, self::ENTREGA);
        }
        if ($n === 2) {
            // the wall's first review in front: the first of La entrega's
            // (a strong one), not whichever the owner happened to add first
            $data['start'] = optional($this->pick($reviews, self::ENTREGA)->first())->id;
        }
        if ($n === 3) {
            $data['beats'] = $this->phrases($reviews);
        }

        return response()
            ->view('muestras.v' . $n, $data)
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /** The reviews the home page shows, in the owner's order, ready to print. */
    private function reviews()
    {
        return Testimonial::where('is_active', true)
            ->whereNotNull('image_path')
            ->orderBy('order_index')->orderBy('id')
            ->get()
            ->filter(fn ($t) => mb_strlen(trim((string) $t->quote)) > 20)   // same floor as the home page
            ->values()
            ->map(function ($t) {
                $paras = self::paragraphs((string) $t->quote);
                $text  = implode(' ', $paras);
                $size  = Img::size($t->image_path) ?: [1200, 1600];
                $ratio = $size[1] > 0 ? $size[0] / $size[1] : 0.75;
                $name  = self::name((string) $t->author_name);

                return (object) [
                    'id'      => (int) $t->id,
                    'name'    => $name,
                    'caption' => $name . ', con su coche',
                    'paras'   => $paras,
                    'text'    => $text,
                    'len'     => mb_strlen($text),
                    'img'     => $t->image_path,
                    'w'       => (int) $size[0],
                    'h'       => (int) $size[1],
                    'ratio'   => round($ratio, 4),
                    // what a box may take; contain mats whatever lies outside
                    'box'     => round(max(0.45, min(2.2, $ratio)), 4),
                ];
            });
    }

    /** The listed reviews that still exist, in the listed order. */
    private function pick($reviews, array $ids)
    {
        $by = $reviews->keyBy('id');
        return collect($ids)->filter(fn ($id) => $by->has($id))->map(fn ($id) => $by[$id])->values();
    }

    /** 3 · each listed review with its phrase, if the phrase is really in it. */
    private function phrases($reviews)
    {
        $by = $reviews->keyBy('id');
        $out = [];
        foreach (self::PHRASES as $id => $p) {
            if ($by->has($id) && mb_strpos($by[$id]->text, $p) !== false) {
                $r = clone $by[$id];
                $r->phrase = $p;
                $out[] = $r;
            }
        }
        return collect($out);
    }

    /** Print-tidy paragraphs. The database row is never touched. */
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

    public static function name(string $n): string
    {
        $n = trim(preg_replace('/\s+/u', ' ', $n));
        // "MIguel" → "Miguel": two capitals then lower case is a held shift key,
        // not an acronym (an acronym has no lower case after it)
        $n = preg_replace_callback('/\b(\p{Lu})(\p{Lu})(?=\p{Ll})/u', fn ($m) => $m[1] . mb_strtolower($m[2]), $n);
        // couples: a no-break space before "&", an ordinary one after
        return preg_replace('/\s*&\s*/u', "\u{00A0}& ", $n);
    }

    /**
     * The opening of a review, cut at a sentence end.
     *
     * Whole sentences are taken while they fit in $limit. Nothing is cut when
     * what is left would be a few words (under 60 characters): a "Leer más"
     * that reveals half a line is a broken promise. When even the first
     * sentence is far over the budget (some reviews are one sentence of 300
     * characters, strung on commas), it is cut at a word with "…".
     * Returns ['short' => string, 'cut' => bool].
     */
    public static function sentences(string $text, int $limit): array
    {
        $text = trim($text);
        $len  = mb_strlen($text);
        if ($len <= $limit + 60) {
            return ['short' => $text, 'cut' => false];
        }
        $parts = preg_split('/(?<=[.!?…])\s+(?=\S)/u', $text);
        $short = '';
        foreach ($parts as $p) {
            $next = $short === '' ? $p : $short . ' ' . $p;
            if (mb_strlen($next) > $limit) {
                // the first sentence alone may run a little over the budget
                if ($short === '' && mb_strlen($p) <= $limit * 1.4) {
                    $short = $p;
                }
                break;
            }
            $short = $next;
        }
        // a first sentence of a few words ("Justo el modelo que estaba
        // buscando.") is an opening, not a quotation: take the next one too
        $taken = $short === '' ? 0 : count(preg_split('/(?<=[.!?…])\s+(?=\S)/u', $short));
        if ($short !== '' && mb_strlen($short) < $limit * .4 && isset($parts[$taken])
            && mb_strlen($short . ' ' . $parts[$taken]) <= $limit * 1.4) {
            $short .= ' ' . $parts[$taken];
        }
        if ($short !== '' && $len - mb_strlen($short) >= 60) {
            return ['short' => $short, 'cut' => true];
        }
        // one long sentence: at a word, never inside one
        $head = mb_substr($text, 0, $limit + 1);
        $sp = mb_strrpos($head, ' ');
        $short = mb_substr($text, 0, $sp === false ? $limit : $sp);
        $short = preg_replace('/[\s,;:.\-–—(«"“]+$/u', '', $short);

        return ['short' => $short . '…', 'cut' => true];
    }

    /**
     * 1 · La entrega's backdrop: the same photograph, blurred far past
     * recognition, pre-rendered once as a ~2 KB file, so the stage never runs
     * a CSS blur on a full-screen layer while it crossfades (that is a filter
     * recomputed per frame on a phone's GPU; a bitmap is not). Made from the
     * 320px derivative (already turned upright), shrunk to 28px, enlarged to
     * 224 and blurred ten passes: smooth at any size it is drawn. Cached
     * under public/img/muestras/amb/, keyed by the photo's path and mtime, so
     * a replaced photograph gets a new backdrop and nobody runs anything.
     */
    public static function ambient(string $img): ?string
    {
        $fs = public_path(ltrim($img, '/'));
        $mt = (int) @filemtime($fs);
        $name = 'img/muestras/amb/' . substr(md5($img . '|' . $mt), 0, 16) . '.webp';
        if (is_file(public_path($name))) {
            return '/' . $name;
        }
        $small = Img::url($img, 320);
        if (!$small || !str_starts_with($small, '/storage/cache/')) {
            return null;
        }
        try {
            $im = @imagecreatefromwebp(storage_path('app/public/cache/' . basename($small)));
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
            @mkdir(dirname(public_path($name)), 0775, true);
            $ok = imagewebp($big, public_path($name), 70);
            imagedestroy($im); imagedestroy($t); imagedestroy($big);
            return $ok ? '/' . $name : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** What the page script needs to draw a review in a viewer. */
    private function json($reviews): string
    {
        $out = $reviews->map(fn ($r) => [
            'id'      => $r->id,
            'name'    => $r->name,
            'caption' => $r->caption,
            'paras'   => $r->paras,
            'w'       => $r->w,
            'h'       => $r->h,
            'src'     => Img::url($r->img, 1080) ?? $r->img,
            'srcset'  => Img::srcset($r->img, 1600),
            'lqip'    => Img::lqip($r->img),
        ])->values();

        return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
