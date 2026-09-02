<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Portada V2.
     *
     * Todo lo que se muestra sale de lo que hay REALMENTE en stock: las categorías
     * y las marcas se derivan de la base de datos, así la página nunca anuncia
     * una categoría vacía (que era el fallo de la versión anterior).
     */
    public function index(Request $request)
    {
        $available = Vehicle::where('status', 'available');

        // --- coches destacados: primero los marcados, después los más recientes
        $featured = (clone $available)
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // --- categorías con existencias (sólo las que tienen coches)
        $bodyTypes = (clone $available)
            ->selectRaw('body_type, COUNT(*) as total')
            ->whereNotNull('body_type')
            ->where('body_type', '!=', '')
            ->groupBy('body_type')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'value' => $row->body_type,
                    'label' => self::BODY_LABELS[$row->body_type] ?? $row->body_type,
                    'total' => (int) $row->total,
                ];
            });

        // --- marcas con existencias
        $brands = (clone $available)
            ->selectRaw('brand, COUNT(*) as total')
            ->groupBy('brand')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['value' => $r->brand, 'total' => (int) $r->total]);

        // --- cifras reales para la franja de confianza
        $stats = [
            'available' => (clone $available)->count(),
            'sold'      => Vehicle::where('status', 'sold')->count(),
            'reviews'   => Testimonial::where('is_active', true)->count(),
            'from'      => (clone $available)->min('price'),
        ];

        // --- opiniones: ahora vienen del controlador, no de una consulta dentro de la vista
        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('order_index')
            ->orderBy('created_at')
            ->get();

        return view('pages.home', compact('featured', 'bodyTypes', 'brands', 'stats', 'testimonials'));
    }

    /** Etiquetas en español para los valores de carrocería guardados en la base. */
    private const BODY_LABELS = [
        'Sedan'       => 'Berlina',
        'Hatchback'   => 'Compacto',
        'Break'       => 'Familiar',
        'Monovolumen' => 'Monovolumen',
        'Furgoneta'   => 'Furgoneta',
        'Todoterreno' => 'Todoterreno',
        'SUV'         => 'SUV',
        'Convertible' => 'Descapotable',
        'Coupe'       => 'Coupé',
    ];
}
