<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Support\Img;
use Illuminate\Http\Response;

/**
 * /muestras/opiniones — a private showroom: ten ways of showing the customer
 * reviews, for the client to choose one. Not linked from anywhere, noindex,
 * not in the sitemap. /muestras/opiniones/{n} renders one proposal on its own,
 * which is what the showroom's device frames load.
 *
 * Every proposal reads the same live data the home page reads, prepared once
 * here so the ten views only decide layout:
 *
 *  - the text is the customer's, untouched in the database. Here it is only
 *    tidied for print: CRLF paragraphs become paragraphs, runs of spaces
 *    collapse, a space before a comma goes, and a review typed entirely inside
 *    quotation marks loses the outer pair (each layout draws its own).
 *  - the name: "Diego&Paula" prints "Diego & Paula" with a no-break space
 *    before the ampersand so a couple never splits across lines on "&";
 *    a slip of the shift key ("MIguel") is printed "Miguel". The record keeps
 *    what the owner typed.
 *  - the photograph's real shape (EXIF-aware, via Img::size) — the box takes
 *    it, so nothing is cropped (CLAUDE.md §5).
 *  - a length tier, so very short reviews are set large instead of floating
 *    in an empty card, and long ones are cut at a word with "Leer más".
 */
class ReviewShowroomController extends Controller
{
    /** The proposals: number => [name, one line]. The shell prints these. */
    /** The proposals: number => [name, what it is, where it fits and what it is best at].
     *  The shell prints these; nothing of it appears inside a proposal. */
    public const VARIANTS = [
        1  => ['Mosaico', 'Todas las fotos enteras en columnas, con un trozo de la opinión debajo. Al tocar una, se lee entera.',
               'Para una página de opiniones o /por-que-nosotros. Lo mejor: muchas entregas a la vista de golpe.'],
        2  => ['Cine', 'Una opinión cada vez, foto grande y texto al lado. Pasa sola, despacio, y se detiene al tocarla.',
               'Para la home. Lo mejor: cada cliente tiene todo el protagonismo y la sección ocupa poca altura.'],
        3  => ['Carril', 'Tarjetas en fila que se deslizan con el dedo, como en una app del teléfono.',
               'Para la home. Lo mejor: en el teléfono se usa con el pulgar, natural y ligero.'],
        4  => ['Muro de fotos', 'Solo las fotos, enteras. Al tocar una se abre la opinión completa, con anterior y siguiente.',
               'Para /por-que-nosotros o una página de opiniones. Lo mejor: el impacto de ver todas las entregas juntas.'],
        5  => ['Protagonista', 'Una opinión en grande y todos los clientes en fila para elegir a quién leer.',
               'Para la home. Lo mejor: una frase grande que se lee de un vistazo, y el visitante elige.'],
        6  => ['Dos filas', 'Dos filas que se mueven despacio en sentidos opuestos y se paran al pasar el dedo o el ratón.',
               'Para la home; es lo más cercano a lo que hay ahora. Lo mejor: muchas opiniones en poca altura, con vida.'],
        7  => ['Foto fija', 'En ordenador la foto se queda quieta a un lado y cambia mientras se leen las opiniones.',
               'Para una página de opiniones. Lo mejor: en ordenador se lee con calma, con la foto siempre a la vista.'],
        8  => ['Álbum', 'Fotos ordenadas como en un álbum, con el nombre debajo. Al tocar una se abre ahí mismo.',
               'Para /por-que-nosotros o una página de opiniones. Lo mejor: ordenado y rápido de recorrer.'],
        9  => ['Las palabras', 'Primero el texto, en letra grande; la foto acompaña en pequeño.',
               'Para /por-que-nosotros. Lo mejor: convencen las palabras; las frases cortas se leen enormes.'],
        10 => ['Destacadas', 'Las tres opiniones más completas, grandes, y el resto al pulsar «Ver todas».',
               'Para la home. Lo mejor: lo más convincente primero, en poca altura; el resto a un toque.'],
    ];

    public function index(): Response
    {
        return response()
            ->view('muestras.opiniones', ['variants' => self::VARIANTS, 'count' => $this->reviews()->count()])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function show(int $n): Response
    {
        abort_unless(isset(self::VARIANTS[$n]), 404);
        $reviews = $this->reviews();

        return response()
            ->view('muestras.v' . $n, [
                'n'        => $n,
                // inside the showroom's frames: no repeated heading, heights follow content
                'embed'    => request()->boolean('embed'),
                'variant'  => self::VARIANTS[$n],
                'reviews'  => $reviews,
                // the most common shape among the photographs: a uniform box drawn
                // at this ratio holds most of them exactly and mats only the rest
                'boxRatio' => $this->medianRatio($reviews),
                'json'     => $this->json($reviews),
            ])
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
                $len   = mb_strlen($text);
                $size  = Img::size($t->image_path) ?: [1200, 1600];
                $ratio = $size[1] > 0 ? $size[0] / $size[1] : 0.75;
                $name  = self::name((string) $t->author_name);

                return (object) [
                    'id'      => (int) $t->id,
                    'name'    => $name,
                    'caption' => $name . ', con su coche',
                    'paras'   => $paras,
                    'text'    => $text,
                    'len'     => $len,
                    // xs: a line · s: a short paragraph · m · l: a letter
                    'tier'    => $len <= 70 ? 'xs' : ($len <= 160 ? 's' : ($len <= 340 ? 'm' : 'l')),
                    'img'     => $t->image_path,
                    'w'       => (int) $size[0],
                    'h'       => (int) $size[1],
                    'ratio'   => round($ratio, 4),
                    // what a box may take; contain mats whatever lies outside
                    'box'     => round(max(0.45, min(2.2, $ratio)), 4),
                    // for a control whose name would otherwise be "Ismael" three times
                    'label'   => $name . ': «' . self::cut($text, 48, false)['short'] . '»',
                ];
            });
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
     * A shorter version of a text, cut at a word and never in the middle of one.
     *
     * Returns ['short' => string, 'cut' => bool]. Nothing is cut when the rest
     * would be a few words — a "Leer más" that reveals half a line is a broken
     * promise. A sentence end in the last 40% of the budget is preferred over
     * a bare word break: a whole sentence then reads as finished.
     */
    public static function cut(string $text, int $limit, bool $slack = true): array
    {
        $text = trim($text);
        $len  = mb_strlen($text);
        if ($len <= $limit + ($slack ? max(40, (int) ($limit * .25)) : 0)) {
            return ['short' => $text, 'cut' => false];
        }
        $head = mb_substr($text, 0, $limit + 1);
        // the last sentence end inside the budget, if it is late enough
        if (preg_match_all('/[.!?…](?=\s)/u', $head, $m, PREG_OFFSET_CAPTURE)) {
            $last = end($m[0]);
            $at   = mb_strlen(substr($head, 0, $last[1])) + 1;
            if ($at >= $limit * .6) {
                return ['short' => rtrim(mb_substr($text, 0, $at)), 'cut' => true];
            }
        }
        $sp = mb_strrpos($head, ' ');
        $short = mb_substr($text, 0, $sp === false ? $limit : $sp);
        $short = preg_replace('/[\s,;:.\-–—(«"“]+$/u', '', $short);

        return ['short' => $short . '…', 'cut' => true];
    }

    private function medianRatio($reviews): float
    {
        $r = $reviews->pluck('box')->sort()->values();
        if ($r->isEmpty()) {
            return 0.75;
        }
        return (float) $r[intdiv($r->count(), 2)];
    }

    /** What the page script needs to draw a review in the viewer. */
    private function json($reviews): string
    {
        $out = $reviews->map(fn ($r) => [
            'id'      => $r->id,
            'name'    => $r->name,
            'caption' => $r->caption,
            'paras'   => $r->paras,
            'tier'    => $r->tier,
            'w'       => $r->w,
            'h'       => $r->h,
            'box'     => $r->box,
            'src'     => Img::url($r->img, 1080) ?? $r->img,
            'srcset'  => Img::srcset($r->img, 1600),
            'lqip'    => Img::lqip($r->img),
        ])->values();

        return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
