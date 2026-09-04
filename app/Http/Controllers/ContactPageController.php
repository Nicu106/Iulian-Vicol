<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\Vehicle;
use Illuminate\View\View;

/**
 * Contact, at /contacto.
 *
 * Ordered by what buyers actually do: over 70% would rather message a dealer than
 * call or fill a form, 55% expect an answer within three hours, and 80% of
 * WhatsApp messages are read within five minutes. A form that lands in an inbox
 * and waits for someone to notice is the slowest path on the page, so it is the
 * last one offered — not the first.
 */
class ContactPageController extends Controller
{
    /** What the old page's dropdown offered, as things a person would actually say. */
    private const REASONS = [
        'Un coche concreto'        => 'Hola, me interesa un coche que he visto en la web.',
        'Probar un coche'          => 'Hola, ¿podría probar uno de los coches?',
        'Financiación'             => 'Hola, quería preguntar por la financiación.',
        'Vender el mío'            => 'Hola, quiero vender mi coche. ¿Te lo puedo enseñar?',
        'Garantía o postventa'     => 'Hola, tengo una pregunta sobre la garantía.',
        'Otra cosa'                => 'Hola,',
    ];

    public function index(): View
    {
        return view('pages.contacto', [
            'reasons'   => self::REASONS,
            'phone'     => '+34 614 753 187',
            'phoneRaw'  => '34614753187',
            'email'     => 'jvmotorclass@gmail.com',
            'available' => Vehicle::where('status', 'available')->count(),
            'sold'      => Vehicle::where('status', 'sold')->count(),
            'people'    => Testimonial::where('is_active', true)->whereNotNull('image_path')->count(),
            // Chosen from four, and not on the numbers — all four measured well and
            // all four spread their subject across every one of the 24 columns the
            // slices cut, so all four survive being sliced. What decided it:
            //   c1  Panamera in snow, plate reads Ontario. The place contradicts Málaga.
            //   c2  A tail-light detail. Beautiful, but sliced it never reads as a car,
            //       and it repeats the 911 already on /inicio.
            //   c4  911 in profile — the cleanest of the four, and from the same
            //       photographer's set as the /inicio hero (ids ...646 and ...711).
            //       It would make two pages look like one.
            //   c3  A Cayman on a GERMAN plate — the blue EU strip and a Berlin B.
            //       The page says five German marques; this photograph says it too.
            //       Its brake light also rhymes with the Porsche red in the palette.
            'hero'      => '/img/banner/contacto.jpg',
            // Where his customers came from — and this is the page's whole argument.
            // The reviews say it themselves: "viaje desde Valladolid", "viaje desde
            // Valencia", "me lo entregaron a la puerta de mi casa". A man with no
            // showroom does not need a pin on a map; he needs to show that people
            // have already driven 700km on his word, and that others never had to.
            //
            // Only cities the review TEXTS name are used. The author_location column
            // says Santander for 19 of 25 and contradicts its own quotes — Domingo's
            // text says Valencia while his column says Santander — so the column is
            // not trusted here. Coordinates are real; x/y are a linear lon/lat
            // projection into a 0-100 box, which is honest over one country.
            'from'      => [
                ['city' => 'Valladolid', 'who' => 'Pablo',          'km' => 720, 'x' => 36.31, 'y' => 27.53,
                 'said' => 'Soy ingeniero mecánico, viaje desde Valladolid, probé el coche y no decepcionó.'],
                ['city' => 'Valencia',   'who' => 'Domingo',        'km' => 630, 'x' => 70.82, 'y' => 55.51,
                 'said' => 'Me enamoré del coche y viajé desde Valencia. Julián me dio confianza desde el primer minuto.'],
                ['city' => 'Granada',    'who' => 'Juanma y Antonio','km' => 130, 'x' => 45.25, 'y' => 84.91,
                 'said' => 'Este fin de semana adquirido un Mercedes C220. La verdad que una compra genial.'],
            ],
            'home'      => ['city' => 'Málaga', 'x' => 38.72, 'y' => 90.75],
            // and the ones who never travelled at all
            'delivered' => ['who' => 'Ismael',
                 'said' => 'Venta online, conversación directa con vídeos muy claros mostrando el coche. Me lo entregaron a la puerta de mi casa.'],
        ]);
    }
}
