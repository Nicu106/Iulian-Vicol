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

        // Everyone else in the photo of their delivery: the three cards flip
        // through them before they land (why-say.js), so the page shows there
        // are many more than three. Small files: they are seen for a blink.
        $shown = $reviews->take(3)->pluck('id');
        $pool = Testimonial::where('is_active', true)->whereNotNull('image_path')
            ->whereNotIn('id', $shown)->get()
            ->map(fn ($t) => ['s' => \App\Support\Img::url($t->image_path, 480) ?? $t->image_path, 'n' => $t->author_name])
            ->values();

        return view('pages.por-que', [
            'pool'        => $pool,
            'sold'        => Vehicle::where('status', 'sold')->count(),
            'reviewCount' => Testimonial::where('is_active', true)->whereNotNull('image_path')->count(),
            'plays'       => 600000,
            'farthest'    => 720,    // km — Pablo, from Valladolid (/contacto, "A 720 km de aquí")
            'reviews'     => $reviews,
        ]);
    }
}
