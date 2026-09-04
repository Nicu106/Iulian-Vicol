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
    /** The vertical room a column gives its text before the photograph starts
     *  losing it, and the width the photograph takes when it moves beside the
     *  text instead. Kept here because the CSS has to lay out the same numbers. */
    private const FB_TEXT_BOX = 360;
    private const FB_PHOTO    = 260;

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
                // 1.35 characters per pixel of column width is what one column of
                // this type at this size holds in the height a slide gives it.
                $w = (int) max(240, min(560, round($len / 1.35)));
                // Characters per line at that width, then the lines they need, then
                // whether a photograph still has somewhere to be.
                $lines = (int) ceil($len / max(18, $w / 8.5));
                $room  = self::FB_TEXT_BOX - $lines * 26;
                // One idea on two axes: the photograph takes what the text does
                // not. A short review leaves height, so the picture sits above it
                // and fills that height. A long one leaves none, so the picture
                // goes beside it and the cell takes the width instead — which is
                // also what stops a lone 753-character review from growing to a
                // 139-character line because it was the only thing on the slide.
                $side = $room < 120;
                return (object) [
                    'name'  => $t->author_name,
                    'quote' => trim($t->quote),
                    'len'   => $len,
                    'w'     => $w,
                    'side'  => $side,
                    // On a phone there is one column and no width to trade, so
                    // the three longest reviews are set a step smaller there —
                    // at the body size they stood 155px past a card that is
                    // already taller than the screen.
                    'xl'    => $len > 600,
                    'cell'  => $w + ($side ? self::FB_PHOTO + 16 : 0),
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
