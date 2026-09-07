<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * "Vende tu coche": a private owner asking whether he will buy theirs.
 *
 * The page it replaces asked for seventeen required fields — an ad title, engine
 * capacity, body type, colour, horsepower — before it would take a phone number.
 * Those are the dealer's job to establish, and a seller who does not know the
 * cylinder capacity of their own car closes the tab. What he actually needs to
 * answer is: what car, roughly what state, a few photographs, how to reach you.
 * Six required fields. Everything else is optional and says so.
 */
class SellCarController extends Controller
{
    /** The five he works with, in the catalogue's own order and colours, so the
     *  tiles on this page and the rows on /catalogo are the same objects. */
    public const MARQUES = [
        ['key' => 'volkswagen', 'name' => 'Volkswagen',    'colour' => '#022254'],
        ['key' => 'audi',       'name' => 'Audi',          'colour' => '#930016'],
        ['key' => 'bmw',        'name' => 'BMW',           'colour' => '#004086'],
        ['key' => 'mercedes',   'name' => 'Mercedes-Benz', 'colour' => '#01172E'],
        ['key' => 'porsche',    'name' => 'Porsche',       'colour' => '#C50007'],
    ];

    /** The site's own vocabulary — Vehicle::getFuelEsAttribute normalises to these. */
    private const FUEL = ['Gasolina', 'Diésel', 'Híbrido', 'Eléctrico'];
    private const GEAR = ['Manual', 'Automático'];

    private const MAX_PHOTOS = 12;
    private const MAX_PHOTO_KB = 12288;   // phone originals in this library run to 11 MB

    public function index(Request $request): View
    {
        return view('pages.sell-car', [
            'marques' => self::MARQUES,
            'fuels'   => self::FUEL,
            'gears'   => self::GEAR,
            'years'   => range((int) date('Y'), 2005),
            'sent'    => $request->boolean('enviado'),
            'maxPhotos' => self::MAX_PHOTOS,
        ]);
    }

    public function store(Request $request)
    {
        $marqueKeys = array_column(self::MARQUES, 'key');

        $data = $request->validate([
            'brand'        => ['required', 'string', 'in:' . implode(',', array_merge($marqueKeys, ['otra']))],
            'brand_other'  => ['nullable', 'string', 'max:60', 'required_if:brand,otra'],
            'model'        => ['required', 'string', 'max:100'],
            'year'         => ['required', 'integer', 'min:1990', 'max:' . date('Y')],
            'mileage'      => ['required', 'integer', 'min:0', 'max:1500000'],
            'fuel'         => ['nullable', 'string', 'in:' . implode(',', self::FUEL)],
            'transmission' => ['nullable', 'string', 'in:' . implode(',', self::GEAR)],
            'price'        => ['nullable', 'integer', 'min:0', 'max:2000000'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'seller_name'  => ['required', 'string', 'max:120'],
            'seller_phone' => ['required', 'string', 'max:30', 'regex:/^[+\d][\d\s().-]{6,}$/'],
            'seller_email' => ['nullable', 'email', 'max:255'],
            'photos'       => ['nullable', 'array', 'max:' . self::MAX_PHOTOS],
            'photos.*'     => ['image', 'mimes:jpeg,png,webp,heic', 'max:' . self::MAX_PHOTO_KB],
        ], [
            'brand.required'        => 'Dime la marca.',
            'brand_other.required_if' => 'Dime qué marca es.',
            'model.required'        => 'Dime el modelo.',
            'year.required'         => 'Dime el año.',
            'mileage.required'      => 'Dime los kilómetros, aunque sea aproximado.',
            'seller_name.required'  => 'Dime cómo te llamas.',
            'seller_phone.required' => 'Necesito un teléfono para contestarte.',
            'seller_phone.regex'    => 'Ese teléfono no parece un teléfono.',
            'photos.max'            => 'Hasta ' . self::MAX_PHOTOS . ' fotos.',
            'photos.*.max'          => 'Una de las fotos pesa más de 12 MB.',
            'photos.*.image'        => 'Uno de los archivos no es una imagen.',
        ]);

        $brandName = $data['brand'] === 'otra'
            ? trim((string) $data['brand_other'])
            : collect(self::MARQUES)->firstWhere('key', $data['brand'])['name'];

        $slug = Str::slug($brandName . ' ' . $data['model'] . ' ' . $data['year'] . ' ' . Str::lower(Str::random(5)));

        // Under /storage/ so the same resizer, cache and warming cover them. Two
        // columns get them: `images` is what the admin's review screen reads, and
        // cover/gallery are what the public page reads once he approves it — the
        // old form filled only the first, so an approved car had no photograph.
        $stored = [];
        foreach ((array) $request->file('photos', []) as $file) {
            $stored[] = '/storage/' . $file->store('sell-cars/' . $slug, 'public');
        }

        Vehicle::create([
            'title'        => trim($brandName . ' ' . $data['model'] . ' ' . $data['year']),
            'brand'        => $brandName,
            'model'        => $data['model'],
            'year'         => $data['year'],
            'mileage'      => $data['mileage'],
            'fuel'         => $data['fuel'] ?? null,
            'fuel_type'    => $data['fuel'] ?? null,
            'transmission' => $data['transmission'] ?? null,
            'price'        => $data['price'] ?? 0,
            'description'  => $data['description'] ?? null,
            'slug'         => $slug,
            'featured'     => false,
            'offer_type'   => 'Compra',
            'seller_name'  => $data['seller_name'],
            'seller_phone' => $data['seller_phone'],
            'seller_email' => $data['seller_email'] ?? null,
            'images'       => $stored,                 // cast to array on the model; json_encode here double-encoded
            'cover_image'  => $stored[0] ?? null,
            'gallery_images' => array_slice($stored, 1),
            'status'       => 'pending',
        ]);

        // A state on the same page, not a flash: the confirmation is content, and it
        // has to survive a refresh.
        return redirect()->route('sell-car', ['enviado' => 1]);
    }
}
