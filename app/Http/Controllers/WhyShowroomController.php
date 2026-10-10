<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * /muestras/por-que — private proposals for /por-que-nosotros rebuilt from the
 * client's photographs (2026-10-09). Not linked, noindex, not in the sitemap.
 * /muestras/por-que/{v} is one whole proposal, opened directly; versions of the
 * chosen approach are kept side by side (2 = as chosen, 2.1 = next iteration, …),
 * each in its own view (v2-1.blade.php) with its own css/js, so going back to an
 * earlier version is only a link. The review chrome (this index, the version bar,
 * the PIN page) is in English; the pages themselves stay Spanish. The chosen
 * version replaces resources/views/pages/por-que.blade.php; then this goes.
 *
 * Each proposal gets exactly the data the live page gets (WhyUsController), so
 * every text and figure stays the live one. Photos: storage/app/public/why/photos.
 */
class WhyShowroomController extends Controller
{
    /** version => [name, what changed, approach] — newest of each approach first in the list */
    public const VARIANTS = [
        '2.1' => ['Gallery 2.1', 'Iteration of 2: ultrawide screens (cover and welcome photos stay whole, sharper images), one text edge, refined details.', 'Gallery'],
        '2'   => ['Gallery 2.0', 'The version the client chose: light magazine, words on the photographed wall.', 'Gallery'],
        '1'   => ['Cinema', 'The story as a film: words on the wall behind the car, depth and cuts.', 'Cinema'],
        '3'   => ['Immersive', 'Full-screen photos you move through by scrolling, from the whole car to the detail.', 'Immersive'],
    ];

    public function index(): Response
    {
        return response()->view('muestras.por-que.index', ['variants' => self::VARIANTS])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function show(string $v): Response
    {
        $view = 'muestras.por-que.v' . str_replace('.', '-', $v);
        abort_unless(isset(self::VARIANTS[$v]) && view()->exists($view), 404);
        $data = (new WhyUsController)->index()->getData();
        $html = view($view, $data + ['variant' => $v, 'variants' => self::VARIANTS])->render();
        // the review bar is added here, not in the page, so every version's page stays
        // exactly as it was designed and can go live as it is
        $bar = view('muestras.por-que._bar', ['current' => $v, 'variants' => self::VARIANTS])->render();
        $html = preg_replace('~</body>~i', $bar . '</body>', $html, 1);
        return response($html)
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
