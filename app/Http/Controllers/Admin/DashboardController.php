<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Testimonial;
use App\Models\Vehicle;
use App\Support\Inbox;

/**
 * The panel's first screen.
 *
 * What it used to be: fifteen variables for seven gradient tiles, three of
 * which showed a permanent zero (Consultas, Destacados, Coches para Vender —
 * those tables have never held a row), a "Resumen del Mes" whose "Vistas
 * totales" was the all-time sum, and an "Actividad Reciente" list that was
 * four hardcoded lines in the template. It announced "Nueva consulta recibida
 * — hace 4 horas" on a site that has never received one. $recentVehicles and
 * $recentInquiries were queried and then never used.
 *
 * What it is now: four numbers that can change, a list of things that actually
 * want him, and the cars that are live. Everything on the page is read from
 * the database, and nothing that is structurally zero is given room.
 */
class DashboardController extends Controller
{
    /** Below this a car is not really listed yet. The median across the 41 cars is 30. */
    private const THIN = 5;

    public function index()
    {
        $vehicles = Vehicle::query()->get();

        $live = $vehicles->where('status', 'available');
        $gone = $vehicles->where('status', 'sold');

        $inbox = Inbox::counts();

        /* Only things that are true right now. An empty list is a good day and
           the page says so, rather than inventing four rows to fill a panel. */
        $todo = [];

        $thin = $live->filter(fn ($v) => count($v->gallery_images ?: []) < self::THIN);
        if ($thin->isNotEmpty()) {
            $todo[] = [
                'n'    => $thin->count(),
                'what' => $thin->count() === 1 ? 'coche con menos de 5 fotos' : 'coches con menos de 5 fotos',
                'why'  => 'La media de tus coches son 30 fotos. Con menos, el anuncio parece a medio hacer.',
                'to'   => route('admin.vehicles.index', ['status' => 'available']),
            ];
        }

        $mute = $live->filter(fn ($v) => trim(strip_tags((string) $v->description)) === '');
        if ($mute->isNotEmpty()) {
            $todo[] = [
                'n'    => $mute->count(),
                'what' => $mute->count() === 1 ? 'coche sin descripción' : 'coches sin descripción',
                'why'  => 'La ficha se queda con los datos técnicos y nada más.',
                'to'   => route('admin.vehicles.index', ['status' => 'available']),
            ];
        }

        $offers = Vehicle::where('offer_type', 'sell')->where('status', 'pending')->count();
        if ($offers > 0) {
            $todo[] = [
                'n'    => $offers,
                'what' => $offers === 1 ? 'coche que alguien te quiere vender' : 'coches que alguien te quiere vender',
                'why'  => 'Llegan del formulario "Vende tu coche".',
                'to'   => route('admin.sell-cars.index'),
            ];
        }

        $tests = Inquiry::count();
        if ($tests > 0) {
            $todo[] = [
                'n'    => $tests,
                'what' => $tests === 1 ? 'solicitud de prueba' : 'solicitudes de prueba',
                'why'  => 'Del formulario de la ficha del coche.',
                'to'   => route('admin.inquiries.index'),
            ];
        }

        if ($inbox['real'] > 0) {
            $todo[] = [
                'n'    => $inbox['real'],
                'what' => $inbox['real'] === 1 ? 'mensaje que parece real' : 'mensajes que parecen reales',
                'why'  => 'De ' . $inbox['total'] . ' recibidos. El resto es spam y está aparte.',
                'to'   => route('admin.contacts.index'),
            ];
        }

        return view('admin.dashboard', [
            'live'      => $live->sortByDesc('created_at')->values(),
            'nLive'     => $live->count(),
            'nGone'     => $gone->count(),
            'nSays'     => Testimonial::where('is_active', true)->count(),
            'nReal'     => $inbox['real'],
            'nMsgTotal' => $inbox['total'],
            'todo'      => $todo,
        ]);
    }
}
