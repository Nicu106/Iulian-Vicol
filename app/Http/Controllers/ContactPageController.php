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
            // His own photograph, not stock: the E350d cabrio with Málaga behind it.
            'hero'      => '/storage/vehicles/mercedes-benz-e350d-bluetec-9g-2016-pg9d3/LbLEXNfUs2rDWJBJ3NDryD1d7xub6RzMBPx3Hsmx.jpg',
            // Four faces for the strip under the map — the people he actually sold to.
            'faces'     => Testimonial::where('is_active', true)->whereNotNull('image_path')
                              ->orderBy('order_index')->take(4)->get(),
            // Where the map points. A dealer who works by appointment has no shopfront,
            // so the map is the city, not a pin on a door that is not there.
            'place'     => 'Málaga, España',
            'mapQuery'  => 'M%C3%A1laga, Espa%C3%B1a',
        ]);
    }
}
