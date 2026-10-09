<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * /muestras/por-que — private proposals for /por-que-nosotros rebuilt from the
 * client's photographs (2026-10-09). Not linked, noindex, not in the sitemap.
 * /muestras/por-que/{n} is one whole proposal, opened directly. The chosen one
 * replaces resources/views/pages/por-que.blade.php; then this goes.
 *
 * Each proposal gets exactly the data the live page gets (WhyUsController), so
 * every text and figure stays the live one. Photos: storage/app/public/why/photos.
 */
class WhyShowroomController extends Controller
{
    public const VARIANTS = [
        1 => ['Cine', 'La historia contada como una película: las palabras en la pared, detrás del coche; profundidad y cortes de montaje.'],
        2 => ['Galería', 'Una revista de coches: fotos grandes en página clara, el texto al lado, ritmo tranquilo.'],
        3 => ['Inmersiva', 'Fotos a pantalla completa que se atraviesan con el scroll: del coche entero al detalle, sin cortes.'],
    ];

    public function index(): Response
    {
        return response()->view('muestras.por-que.index', ['variants' => self::VARIANTS])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function show(int $n): Response
    {
        abort_unless(isset(self::VARIANTS[$n]) && view()->exists("muestras.por-que.v$n"), 404);
        $data = (new WhyUsController)->index()->getData();
        return response()->view("muestras.por-que.v$n", $data + ['variant' => $n, 'variants' => self::VARIANTS])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
