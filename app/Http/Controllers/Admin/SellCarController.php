<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SellCarController as PublicSellCar;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellCarController extends Controller
{
    public function index()
    {
        /* This looked for offer_type 'Vânzare' — Romanian, from the old site —
           while the public form has always written 'Compra'. The page could not
           have shown a single offer no matter how many arrived. Both ends read
           the same constant now. Approved and rejected ones stay in the list
           with their state on the row, so nothing a seller sent disappears. */
        $vehicles = Vehicle::where('offer_type', PublicSellCar::OFFER_TYPE)
                          ->orderBy('created_at', 'desc')
                          ->paginate(15);

        // Decode images for each vehicle
        $vehicles->getCollection()->transform(function ($vehicle) {
            if ($vehicle->images && is_string($vehicle->images)) {
                $vehicle->images = json_decode($vehicle->images, true);
            }
            return $vehicle;
        });

        return view('admin.sell-cars.index', compact('vehicles'));
    }

    /**
     * A stored photograph, as a key on the 'public' disk.
     *
     * The public form stores "/storage/sell-cars/{slug}/{file}" — a URL, so the
     * page can print it — and every consumer in this class treated it as a disk
     * key instead. Storage::delete() therefore deleted nothing (files were
     * orphaned on every removal), and downloadPhotos() built
     * storage/app/public/storage/sell-cars/... and produced an EMPTY zip for
     * every real offer. One conversion, in one place, tolerant of both shapes
     * because rows written by the old admin edit are already the other one.
     */
    private static function key(string $stored): string
    {
        $s = ltrim($stored, '/');

        return str_starts_with($s, 'storage/') ? substr($s, strlen('storage/')) : $s;
    }

    public function show(Vehicle $vehicle)
    {
        // Decode images if it's a string
        if ($vehicle->images && is_string($vehicle->images)) {
            $vehicle->images = json_decode($vehicle->images, true);
        }
        
        return view('admin.sell-cars.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        // Decode images if it's a string
        if ($vehicle->images && is_string($vehicle->images)) {
            $vehicle->images = json_decode($vehicle->images, true);
        }
        
        return view('admin.sell-cars.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'mileage' => 'required|integer|min:0',
            /* The public form writes Spanish (App\Http\Controllers\SellCarController::FUEL
               and ::GEAR). These two lists were Romanian — "Benzină, Diesel,
               Hibrid, Electric" and "Manuală, Automată" — left over from the old
               site, so any offer that actually arrived through /vende would have
               been refused the moment he opened it and pressed save. Read from
               the constants now, so the two cannot drift apart again. */
            'fuel_type' => 'required|string|in:' . implode(',', \App\Http\Controllers\SellCarController::FUEL),
            'transmission' => 'required|string|in:' . implode(',', \App\Http\Controllers\SellCarController::GEAR),
            'body_type' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'engine_capacity' => 'required|integer|min:0',
            'power' => 'required|integer|min:0',
            'description' => 'required|string|max:2000',
            'seller_name' => 'required|string|max:255',
            'seller_phone' => 'required|string|max:20',
            'seller_email' => 'required|email|max:255',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max per image
        ]);

        // Handle new image uploads
        $newImagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                /* Same shape the public form writes, or the array ends up
                   half URLs and half disk keys and only half of it displays. */
                $newImagePaths[] = '/storage/' . $image->store('sell-cars/' . $vehicle->slug, 'public');
            }
        }

        // Get existing images
        $existingImages = $vehicle->images ? (is_string($vehicle->images) ? json_decode($vehicle->images, true) : $vehicle->images) : [];
        
        // Remove images that were marked for deletion
        if ($request->has('removed_images') && $request->removed_images) {
            $removedImages = json_decode($request->removed_images, true);
            if (is_array($removedImages)) {
                // Delete files from storage
                foreach ($removedImages as $removedImage) {
                    Storage::disk('public')->delete(self::key($removedImage));
                }
                // Remove from existing images array
                $existingImages = array_values(array_diff($existingImages, $removedImages));
            }
        }
        
        // Combine existing and new images
        $allImages = array_merge($existingImages, $newImagePaths);

        // Update vehicle record
        $vehicle->update([
            'title' => $request->title,
            'brand' => $request->brand,
            'model' => $request->model,
            'year' => $request->year,
            'price' => $request->price,
            'mileage' => $request->mileage,
            'fuel_type' => $request->fuel_type,
            'transmission' => $request->transmission,
            'body_type' => $request->body_type,
            'color' => $request->color,
            'engine_capacity' => $request->engine_capacity,
            'power' => $request->power,
            'description' => $request->description,
            'seller_name' => $request->seller_name,
            'seller_phone' => $request->seller_phone,
            'seller_email' => $request->seller_email,
            /* 'images' is cast to array on the model. Encoding it here stored
               a JSON string INSIDE a JSON column, so the photographs of any
               offer edited in the panel came back double-encoded and did not
               render. The public form already had this fixed; this end did not. */
            'images' => array_values($allImages),
        ]);

        return redirect()->route('admin.sell-cars.show', $vehicle)
                        ->with('status', 'Guardado.');
    }

    public function approve(Vehicle $vehicle)
    {
        /* 'is_featured' was neither a column nor fillable, so mass-assignment
           protection dropped it silently on every approval. The column is
           'featured'. */
        $vehicle->update([
            'status'   => 'available',
            'featured' => false,
        ]);

        return redirect()->route('admin.sell-cars.index')
                        ->with('status', 'Publicado. Ya está en el catálogo.');
    }

    public function reject(Vehicle $vehicle)
    {
        $vehicle->update([
            'status' => 'rejected'
        ]);

        return redirect()->route('admin.sell-cars.index')
                        ->with('status', 'Rechazado. Sigue aquí por si cambias de idea.');
    }

    public function destroy(Vehicle $vehicle)
    {
        // Delete associated images
        if ($vehicle->images) {
            $images = is_string($vehicle->images) ? json_decode($vehicle->images, true) : $vehicle->images;
            if (is_array($images)) {
                foreach ($images as $image) {
                    Storage::disk('public')->delete(self::key($image));
                }
            }
        }

        $vehicle->delete();

        return redirect()->route('admin.sell-cars.index')
                        ->with('status', 'Oferta borrada.');
    }

    /**
     * A ZIP of every photograph the seller uploaded.
     */
    public function downloadPhotos(Vehicle $vehicle)
    {
        $images = $vehicle->images;
        if (is_string($images)) {
            $images = json_decode($images, true);
        }
        $images = is_array($images) ? array_values($images) : [];

        if (empty($images)) {
            return redirect()->back()->with('error', 'Esta oferta no tiene fotos.');
        }

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) { @mkdir($tmpDir, 0755, true); }
        $zipName = 'fotos-' . ($vehicle->slug ?? $vehicle->id) . '-' . date('Ymd_His') . '.zip';
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'No se ha podido preparar el archivo.');
        }

        foreach ($images as $imgPath) {
            $absolute = storage_path('app/public/' . self::key($imgPath));
            if (is_file($absolute)) {
                $zip->addFile($absolute, basename($absolute));
            }
        }

        $zip->close();
        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}
