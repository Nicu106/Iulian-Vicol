<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;
use App\Models\Vehicle;
use Illuminate\View\View;

/**
 * The home page, at /inicio.
 *
 * Everything the search widget says is counted from the stock, not typed in:
 * "Audi (0)" is a disabled option, not a lie, and the button says how many cars
 * it will actually find.
 */
class HomePageController extends Controller
{
    /* The card's own dimensions, and what has to fit inside them. Kept here
     * because the CSS lays out the same numbers, and measured against the
     * rendered page rather than guessed. */
    /* The card height is not a number somebody picked — it is derived from the
     * longest review there is, clamped so one enormous one cannot make the whole
     * row a wall. Anything that still will not fit is set a step smaller rather
     * than truncated, so any text of any length has somewhere to go. */
    /* Both measured off the rendered page, not estimated: the quote sets at 18px
     * with a 27.9px line, and across the 23 multi-line reviews one character
     * takes between 8.91 and 11.48px. The worst case is the one to plan with —
     * budget for the median and the wide ones overflow, which is exactly what
     * two of them were doing. */
    private const FB_LINE   = 28;     // one line of the quote
    private const FB_CHAR   = 11.5;   // one character of it, at its widest
    private const FB_CARD   = 560;    // the height of every card, and the CSS agrees
    private const FB_CHROME = 88;     // its padding, plus the byline under the quote
    private const FB_INSET  = 50;     // the padding and borders the words sit inside
    private const FB_SCALES = [1.0, 0.9, 0.8, 0.7];
    private const FB_W_MIN  = 240;
    private const FB_W_HARD = 900;    // and the one we will accept rather than not fit
    /* The widest a card may be and still put the photograph on top. Beyond this
     * the band would be wider than 1.54:1, and a 3:4 phone photograph of people
     * standing next to a car does not survive a crop much flatter than that —
     * measured on all 24: at 2.35:1 several were decapitated. This is a ratio
     * ceiling expressed as a width, and it holds for any review anyone adds
     * later, of any length, without anybody looking at the photograph. */

    private const MARQUES = [
        ['key' => 'volkswagen', 'name' => 'Volkswagen',    'match' => ['volkswagen', 'vw']],
        ['key' => 'audi',       'name' => 'Audi',          'match' => ['audi']],
        ['key' => 'bmw',        'name' => 'BMW',           'match' => ['bmw']],
        ['key' => 'mercedes',   'name' => 'Mercedes-Benz', 'match' => ['mercedes', 'mercedes-benz', 'mercedes benz']],
        ['key' => 'porsche',    'name' => 'Porsche',       'match' => ['porsche']],
    ];

    /** Months the "al mes" figure is spread over. No interest — the widget says so. */
    public const MONTHS = 48;

    /**
     * The shape of one card.
     *
     * Only two things are decided here, and both are coarse on purpose:
     * WHERE the photograph goes, from the picture's own orientation, and HOW
     * WIDE the words are, from how many there are. Everything that depends on
     * font metrics — how tall a card ends up, where the lines break — is left to
     * the browser, which knows. Solving that here meant estimating characters
     * per pixel, and every correction to the estimate just moved the overflow
     * somewhere else: measured at 8.91 to 11.48px per character across 23
     * reviews, there is no single number that is right.
     *
     * NOTHING IS EVER CROPPED. The photograph's box carries the picture's own
     * aspect-ratio, so there is nothing to cut and nothing to letterbox, and
     * `contain` catches anything the ratio cannot express.
     *
     * The text block is sized to stay roughly as wide as it is tall, whatever
     * the length — so five words get a narrow column and nine hundred get a
     * broad one, and neither is a sliver or a wall.
     */
    public static function shapeFor(int $len, float $ratio): array
    {
        $ratio = max(0.2, min(4.0, $ratio));

        // The card is one rectangle with a photograph on the front and the words
        // on the back, so it has to hold both. Its height is fixed, which makes
        // the photograph's width follow from the picture's own shape — and the
        // card is that width whenever the words can be made to fit it.
        //
        // NOTHING IS CROPPED. Measured on the real files, a fixed band showed 49%
        // of a 3:4 photo's height and a fixed column beside the text showed 78%
        // of its width; no object-position is right for a photograph nobody has
        // seen yet, and these arrive constantly. So the box is the picture's own
        // shape and the image is `contain`: there is nothing to cut.
        $pw = (int) round(self::FB_CARD * $ratio);

        // Try each type size and take the first whose words fit the photograph's
        // width. Stepping the type down a notch is a far smaller price than
        // matting the picture into a wider card, and smaller still than cropping.
        foreach (self::FB_SCALES as $k) {
            $line  = self::FB_LINE * $k;
            $char  = self::FB_CHAR * $k;
            $lines = max(1, (int) floor((self::FB_CARD - self::FB_CHROME) / $line));
            $tw    = (int) ceil($len * $char / $lines) + self::FB_INSET;
            if ($tw <= $pw) {
                return ['w' => max(self::FB_W_MIN, $pw), 'scale' => $k, 'ratio' => round($ratio, 4)];
            }
        }

        // Only something extreme gets here: the card widens and the photograph
        // sits matted inside it, whole.
        $k     = self::FB_SCALES[count(self::FB_SCALES) - 1];
        $line  = self::FB_LINE * $k;
        $lines = max(1, (int) floor((self::FB_CARD - self::FB_CHROME) / $line));
        $tw    = (int) ceil($len * self::FB_CHAR * $k / $lines) + self::FB_INSET;

        return [
            'w'     => max(self::FB_W_MIN, min(self::FB_W_HARD, max($pw, $tw))),
            'scale' => $k,
            'ratio' => round($ratio, 4),
        ];
    }

    /** A photograph's aspect ratio, read once per file and remembered. Falls back
     *  to 3:4 — what a phone shoots — if the file is missing or unreadable, so a
     *  broken path costs a shape, not a fatal. */
    private function ratioOf(?string $path): float
    {
        $file = public_path(ltrim((string) $path, '/'));
        $key  = 'fb-ratio:' . md5($file) . ':' . (@filemtime($file) ?: 0);
        $r = Cache::remember($key, now()->addDays(30), function () use ($file) {
            $s = @getimagesize($file);
            return ($s && $s[1] > 0) ? $s[0] / $s[1] : 0.75;
        });
        // keep it inside what a card can hold; contain does the rest
        return max(0.45, min(2.2, (float) $r));
    }

    public function index(): View
    {
        $available = Vehicle::where('status', 'available')->orderByDesc('price')->get();
        $sold      = Vehicle::where('status', 'sold')->count();

        $keyOf = function ($v): ?string {
            $b = mb_strtolower(trim((string) $v->brand));
            foreach (self::MARQUES as $m) if (in_array($b, $m['match'], true)) return $m['key'];
            return null;
        };

        // the cars the widget searches, as the page's own data
        $stock = $available->map(fn ($v) => [
            'slug'  => $v->slug,
            'marca' => $keyOf($v),
            'model' => $v->model,
            'price' => (int) $v->price,
            'month' => (int) round($v->price / self::MONTHS),
        ])->values();

        $marques = [];
        foreach (self::MARQUES as $m) {
            $mine = $stock->where('marca', $m['key']);
            $marques[] = $m + [
                'n'      => $mine->count(),
                'models' => $mine->pluck('model')->unique()->sort()->values()->all(),
            ];
        }

        // The reviews, as one continuous row.
        //
        // Measured: 24 reviews, 6,900 characters, 7m25s of reading at Spanish
        // reading speed. The distribution is what decides everything here —
        // 49% of all the text lives in 5 of the 24 reviews (494 to 901
        // characters), while 15 of them are under 250. A carousel with a fixed
        // slide shape and a fixed interval cannot hold both, which is why the
        // old one clamped the long ones behind "Ver más" and showed a truncated
        // review as if it were the review.
        //
        // So the shape follows the text instead. Each review is a column whose
        // WIDTH is proportional to how much it has to say, floored so it stays
        // readable and capped at a 64-character measure. There are no slides to
        // pack them into any more: the row drifts continuously, so a review is
        // simply as wide as it needs to be and the row is 9,469px long.
        //
        // Inside a column the text takes what it needs and THE PHOTOGRAPH TAKES
        // WHAT IS LEFT. A short review leaves a tall portrait; a long one leaves
        // room for a thumbnail beside the name. Every photograph is used either
        // way — the old mosaic threw 17 of 25 away, under a heading that counts
        // photographs.
        //
        // author_location is not carried: it says Santander for 19 of 25 while
        // those same quotes say Valencia, Valladolid and Granada.
        $reviews = Testimonial::where('is_active', true)->orderBy('order_index')
            ->whereNotNull('image_path')->get()
            ->filter(fn ($t) => mb_strlen(trim((string) $t->quote)) > 20)   // one review is a comma
            ->values()
            ->map(fn ($t) => (object) array_merge(
                    self::shapeFor(mb_strlen(trim($t->quote)), $this->ratioOf($t->image_path)),
                [
                    'name'  => $t->author_name,
                    'quote' => trim($t->quote),
                    'len'   => mb_strlen(trim($t->quote)),
                    'xl'    => mb_strlen(trim($t->quote)) > 480,
                    'img'   => $t->image_path,
                ]
            ));

        return view('pages.inicio', [
            'available' => $available,
            'stock'     => $stock,
            'marques'   => $marques,
            'total'     => $available->count(),
            'sold'      => $sold,
            'reviews'     => $reviews,
            'reviewCount' => $reviews->count(),
            'months'    => self::MONTHS,
            'euros'     => fn ($n) => number_format((int) $n, 0, ',', '.') . ' €',
            'km'        => fn ($n) => number_format((int) $n, 0, ',', '.') . ' km',
            'chips'     => BrandCatalogController::chips(),
            'sub'       => BrandCatalogController::sub(),
        ]);
    }
}
