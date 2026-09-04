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
        ]);
    }
}
