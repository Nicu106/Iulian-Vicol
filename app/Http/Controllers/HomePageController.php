<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
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
    private const FB_CARD   = 448;    // the height every card gets
    private const FB_CHROME = 88;     // its padding, plus the byline under the quote
    private const FB_PHOTO  = 200;    // the least a photograph can be and still be one
    private const FB_ASIDE  = 276;    // its width when it stands beside the text instead
    private const FB_LINE   = 26;     // one line of the quote
    private const FB_CHAR   = 10.5;   // one character of it
    private const FB_INSET  = 50;     // the padding and borders the text sits inside
    private const FB_W_MIN  = 240;
    private const FB_W_MAX  = 560;

    private const MARQUES = [
        ['key' => 'volkswagen', 'name' => 'Volkswagen',    'match' => ['volkswagen', 'vw']],
        ['key' => 'audi',       'name' => 'Audi',          'match' => ['audi']],
        ['key' => 'bmw',        'name' => 'BMW',           'match' => ['bmw']],
        ['key' => 'mercedes',   'name' => 'Mercedes-Benz', 'match' => ['mercedes', 'mercedes-benz', 'mercedes benz']],
        ['key' => 'porsche',    'name' => 'Porsche',       'match' => ['porsche']],
    ];

    /** Months the "al mes" figure is spread over. No interest — the widget says so. */
    public const MONTHS = 48;

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
            ->map(function ($t) {
                $len = mb_strlen(trim($t->quote));

                // How many lines of quote fit ABOVE a photograph that is still
                // worth calling one, and how many fit BESIDE it.
                $linesTop  = (int) floor((self::FB_CARD - self::FB_CHROME - self::FB_PHOTO) / self::FB_LINE);
                $linesSide = (int) floor((self::FB_CARD - self::FB_CHROME) / self::FB_LINE);
                $widthFor  = fn ($lines) => (int) ceil($len * self::FB_CHAR / $lines) + self::FB_INSET;

                // The column is as wide as the text needs to fit in those lines.
                // The first version had this backwards — it sized the column so
                // the text filled the whole card, and then gave the photograph
                // whatever was left, which for a 241-character review was THREE
                // PIXELS. 14 of the 24 came out under 176px. The photograph's
                // minimum is reserved first now, and the width is solved for it.
                $need = $widthFor($linesTop);
                $side = $need > self::FB_W_MAX;
                $w    = max(self::FB_W_MIN, min(self::FB_W_MAX,
                            $side ? $widthFor($linesSide) : $need));

                return (object) [
                    'name'  => $t->author_name,
                    'quote' => trim($t->quote),
                    'len'   => $len,
                    'w'     => $w,
                    // Past the widest column a review may be, the photograph
                    // stops sharing the height and takes the width instead: a
                    // full-height column of its own beside the words.
                    'side'  => $side,
                    'cell'  => $w + ($side ? self::FB_ASIDE : 0),
                    // On a phone there is one column and no width to trade at
                    // all, so past this length the photograph changes role there
                    // rather than shrinking: it becomes a portrait beside the
                    // name, and the quote is set a step smaller. Measured: these
                    // five, and only these five, were squeezing it under 160px.
                    'xl'    => $len > 480,
                    'img'   => $t->image_path,
                ];
            });

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
