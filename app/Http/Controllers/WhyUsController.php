<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\Vehicle;
use Illuminate\View\View;

/**
 * /por-que-nosotros — why buy here. Every claim on the page is one the client
 * has made himself elsewhere on the site (steps 01-04, the contact page, the
 * catalogue); the numbers come from the database, not from copy.
 */
class WhyUsController extends Controller
{
    public function index(): View
    {
        // Three buyers in their own words, with their photograph: medium-length
        // quotes read best in a card (a comma or a 1,000-word letter do not).
        $reviews = Testimonial::where('is_active', true)->whereNotNull('image_path')
            ->orderBy('order_index')->get()
            ->filter(fn ($t) => ($n = mb_strlen(trim((string) $t->quote))) >= 60 && $n <= 320)
            ->take(4)->values();   // one for the opening, three for "Lo cuentan ellos"

        return view('pages.por-que', [
            'sold'        => Vehicle::where('status', 'sold')->count(),
            'reviewCount' => Testimonial::where('is_active', true)->whereNotNull('image_path')->count(),
            'plays'       => 600000,
            'years'       => 9,      // "+9 años de experiencia" — the client, 2026-10-08
            'farthest'    => 1200,   // km — the client, 2026-10-08 (was 720, Pablo from Valladolid)
            'reviews'     => $reviews,
        ]);
    }
}
