<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellCarController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::where('offer_type', 'Vânzare')
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
            'fuel_type' => 'required|string|in:Benzină,Diesel,Hibrid,Electric',
            'transmission' => 'required|string|in:Manuală,Automată',
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
                $path = $image->store('sell-cars/' . $vehicle->slug, 'public');
                $newImagePaths[] = $path;
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
                    Storage::disk('public')->delete($removedImage);
                }
                // Remove from existing images array
                $existingImages = array_diff($existingImages, $removedImages);
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
            'images' => json_encode($allImages),
        ]);

        return redirect()->route('admin.sell-cars.show', $vehicle)
                        ->with('success', 'Anunțul a fost actualizat cu succes!');
    }

    public function approve(Vehicle $vehicle)
    {
        $vehicle->update([
            'status' => 'available',
            'is_featured' => false
        ]);

        return redirect()->route('admin.sell-cars.index')
                        ->with('success', 'Anunțul a fost aprobat și publicat cu succes!');
    }

    public function reject(Vehicle $vehicle)
    {
        $vehicle->update([
            'status' => 'rejected'
        ]);

        return redirect()->route('admin.sell-cars.index')
                        ->with('success', 'Anunțul a fost respins.');
    }

    public function destroy(Vehicle $vehicle)
    {
        // Delete associated images
        if ($vehicle->images) {
            $images = is_string($vehicle->images) ? json_decode($vehicle->images, true) : $vehicle->images;
            if (is_array($images)) {
                foreach ($images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $vehicle->delete();

        return redirect()->route('admin.sell-cars.index')
                        ->with('success', 'Anunțul a fost șters cu succes!');
    }

    /**
     * Descarcă o arhivă ZIP cu toate pozele încărcate de proprietar
     */
    public function downloadPhotos(Vehicle $vehicle)
    {
        $images = $vehicle->images;
        if (is_string($images)) {
            $images = json_decode($images, true);
        }
        $images = is_array($images) ? array_values($images) : [];

        if (empty($images)) {
            return redirect()->back()->with('success', 'Nu există imagini de descărcat.');
        }

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) { @mkdir($tmpDir, 0755, true); }
        $zipName = 'poze-proprietar-' . ($vehicle->slug ?? $vehicle->id) . '-' . date('Ymd_His') . '.zip';
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('success', 'Nu s-a putut crea arhiva ZIP.');
        }

        foreach ($images as $imgPath) {
            $absolute = storage_path('app/public/' . ltrim($imgPath, '/'));
            if (is_file($absolute)) {
                $zip->addFile($absolute, basename($absolute));
            }
        }

        $zip->close();
        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}
