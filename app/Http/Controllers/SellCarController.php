<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Http\Requests\SellCarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
    public const FUEL = ['Gasolina', 'Diésel', 'Híbrido', 'Eléctrico'];
    public const GEAR = ['Manual', 'Automático'];

    public const MAX_PHOTOS = 12;
    public const MAX_PHOTO_KB = 12288;   // phone originals in this library run to 11 MB
    /** Twelve at 12 MB is 144 MB, which no honest submission reaches and a
     *  malicious one would aim for. Past this the extra photographs are dropped
     *  and the car is still saved: losing a seller over their tenth picture is a
     *  worse outcome than storing nine. */
    public const MAX_TOTAL_BYTES = 60 * 1024 * 1024;

    public function index(Request $request): View
    {
        return view('pages.sell-car', [
            'marques' => self::MARQUES,
            'fuels'   => self::FUEL,
            'gears'   => self::GEAR,
            'years'   => range((int) date('Y'), 2005),
            'sent'    => $request->boolean('enviado'),
            'maxPhotos' => self::MAX_PHOTOS,
            // Encrypted, so the clock in SellCarRequest cannot be back-dated.
            'stamp'   => Crypt::encryptString((string) time()),
        ]);
    }

    public function store(SellCarRequest $request)
    {
        $data = $request->validated();

        $brandName = $data['brand'] === 'otra'
            ? trim((string) $data['brand_other'])
            : collect(self::MARQUES)->firstWhere('key', $data['brand'])['name'];

        $slug = Str::slug($brandName . ' ' . $data['model'] . ' ' . $data['year'] . ' ' . Str::lower(Str::random(5)));

        // Under /storage/ so the same resizer, cache and warming cover them. Two
        // columns get them: `images` is what the admin's review screen reads, and
        // cover/gallery are what the public page reads once he approves it — the
        // old form filled only the first, so an approved car had no photograph.
        //
        // Every file is opened and read before it is kept. `mimetypes` checks what
        // the server sniffs, which is stronger than the browser's claim, but a
        // JPEG with a payload welded on the end still sniffs as a JPEG. getimagesize
        // has to agree that there are real pixels, and ->store() names the file
        // itself from that — the client's own filename never reaches the disk.
        $stored = [];
        $bytes = 0;
        foreach ((array) $request->file('photos', []) as $file) {
            $info = @getimagesize($file->getRealPath());
            if (!$info || $info[0] < 200 || $info[1] < 200) {
                continue;
            }
            $bytes += $file->getSize();
            if ($bytes > self::MAX_TOTAL_BYTES) {
                break;   // the rest are dropped rather than the submission lost
            }
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
