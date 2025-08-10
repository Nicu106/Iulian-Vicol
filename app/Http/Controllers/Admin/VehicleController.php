<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\Vehicle;

class VehicleController extends BaseController
{
    private function readStore(): array
    {
        $storePath = storage_path('app/vehicles.json');
        if (File::exists($storePath)) {
            $json = File::get($storePath);
            return json_decode($json, true) ?: [];
        }
        return [];
    }

    private function writeStore(array $list): void
    {
        $storePath = storage_path('app/vehicles.json');
        File::put($storePath, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $status = $request->get('status');
        $featured = $request->get('featured');
        $sort = $request->get('sort');
        
        try {
            $query = Vehicle::query();
            
            // Apply filters
            if ($q !== '') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('brand', 'like', "%$q%")
                        ->orWhere('model', 'like', "%$q%")
                        ->orWhere('title', 'like', "%$q%");
                });
            }
            
            if ($status) {
                $query->where('status', $status);
            }
            
            if ($featured !== null && $featured !== '') {
                $query->where('featured', (bool) $featured);
            }
            
            // Apply sorting
            switch ($sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'views':
                    $query->orderByDesc('views_count');
                    break;
                default:
                    $query->orderByDesc('created_at');
            }
            
            $items = $query->get()->map(fn($m) => $m->toArray())->all();
        } catch (\Throwable $e) {
            // Fallback to JSON store if DB not migrated
            $items = $this->readStore();
            if ($q !== '') {
                $items = array_values(array_filter($items, function ($v) use ($q) {
                    $hay = strtolower(($v['title'] ?? '') . ' ' . ($v['brand'] ?? '') . ' ' . ($v['model'] ?? ''));
                    return str_contains($hay, strtolower($q));
                }));
            }
            usort($items, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        }
        return view('admin.vehicles.index', compact('items', 'q', 'status', 'featured', 'sort'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:150',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'offer_expires_at' => 'nullable|date|after:today',
            'status' => 'nullable|in:available,reserved,sold,maintenance,draft',
            'featured' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:999',
            'badges' => 'nullable|string',
            'mileage' => 'nullable|string|max:50',
            'fuel' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:50',
            'engine' => 'nullable|string|max:50',
            'power' => 'nullable|string|max:50',
            'drivetrain' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'condition' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'video_url' => 'nullable|url',
            'cover_image' => 'nullable|image',
            'gallery_images.*' => 'nullable|image',
            'location' => 'nullable|string|max:200',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        $slug = Str::slug($validated['brand'] . ' ' . $validated['model'] . ' ' . $validated['year'] . ' ' . Str::random(5));

        // Store uploads in storage/app/public/vehicles/{slug}
        $coverUrl = null;
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('vehicles/' . $slug, 'public');
            $coverUrl = Storage::url($path);
            \Log::info('Cover image uploaded: ' . $request->file('cover_image')->getClientOriginalName());
        }

        $galleryUrls = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $path = $img->store('vehicles/' . $slug, 'public');
                $galleryUrls[] = Storage::url($path);
            }
        }

        $features = array_filter(array_map('trim', explode(',', $validated['features'] ?? '')));
        $badges = array_filter(array_map('trim', explode(',', $validated['badges'] ?? '')));
        $tags = array_filter(array_map('trim', explode(',', $validated['tags'] ?? '')));

        $vehicle = [
            'slug' => $slug,
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'year' => (int) $validated['year'],
            'title' => $validated['brand'] . ' ' . $validated['model'] . ' ' . $validated['year'],
            'price' => $validated['price'],
            'original_price' => $validated['original_price'] ?? null,
            'offer_price' => $validated['offer_price'] ?? null,
            'has_offer' => !empty($validated['offer_price']),
            'offer_expires_at' => $validated['offer_expires_at'] ?? null,
            'status' => $validated['status'] ?? 'available',
            'featured' => isset($validated['featured']),
            'priority' => $validated['priority'] ?? 0,
            'badges' => $badges,
            'mileage' => $validated['mileage'] ?? null,
            'fuel' => $validated['fuel'] ?? null,
            'transmission' => $validated['transmission'] ?? null,
            'engine' => $validated['engine'] ?? null,
            'power' => $validated['power'] ?? null,
            'drivetrain' => $validated['drivetrain'] ?? null,
            'color' => $validated['color'] ?? null,
            'vin' => $validated['vin'] ?? null,
            'condition' => $validated['condition'] ?? null,
            'description' => $validated['description'] ?? null,
            'features' => $features,
            'video_url' => $validated['video_url'] ?? null,
            'cover_image' => $coverUrl,
            'gallery_images' => $galleryUrls,
            'location' => $validated['location'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'tags' => $tags,
            'internal_notes' => $validated['internal_notes'] ?? null,
        ];

        // Append to storage file (mock DB)
        // Persist to DB if table exists, else fallback to JSON store
        try {
            Vehicle::query()->create($vehicle);
        } catch (\Throwable $e) {
            $list = $this->readStore();
            $list[] = $vehicle;
            $this->writeStore($list);
        }

        return redirect()->route('admin.vehicles.index')
            ->with('status', 'Vehicul adăugat cu succes! Slug: ' . $slug)
            ->with('preview_url', route('vehicle.show', ['slug' => $slug]));
    }

    public function edit(string $slug)
    {
        $vehicle = Vehicle::query()->where('slug', $slug)->first();
        if (!$vehicle) {
            $items = $this->readStore();
            $vehicle = collect($items)->firstWhere('slug', $slug);
        }
        abort_unless($vehicle, 404);
        return view('admin.vehicles.edit', ['vehicle' => is_array($vehicle) ? $vehicle : $vehicle->toArray()]);
    }

    public function update(Request $request, string $slug)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:150',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'offer_expires_at' => 'nullable|date|after:today',
            'status' => 'nullable|in:available,reserved,sold,maintenance,draft',
            'featured' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:999',
            'badges' => 'nullable|string',
            'mileage' => 'nullable|string|max:50',
            'fuel' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:50',
            'engine' => 'nullable|string|max:50',
            'power' => 'nullable|string|max:50',
            'drivetrain' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'condition' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'video_url' => 'nullable|url',
            'cover_image' => 'nullable|image',
            'gallery_images.*' => 'nullable|image',
            'location' => 'nullable|string|max:200',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        $vehicle = Vehicle::query()->where('slug', $slug)->first();
        $isDbVehicle = (bool) $vehicle;
        
        if (!$vehicle) {
            $items = $this->readStore();
            $vehicle = collect($items)->firstWhere('slug', $slug);
            abort_unless($vehicle, 404);
        }
        
        $vehicleData = is_array($vehicle) ? $vehicle : $vehicle->toArray();

        // Handle file uploads using Storage
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('vehicles/' . $slug, 'public');
            $vehicleData['cover_image'] = Storage::url($path);
            \Log::info('Cover image updated: ' . $request->file('cover_image')->getClientOriginalName());
        }

        if ($request->hasFile('gallery_images')) {
            $galleryUrls = $vehicleData['gallery_images'] ?? [];
            foreach ($request->file('gallery_images') as $img) {
                $path = $img->store('vehicles/' . $slug, 'public');
                $galleryUrls[] = Storage::url($path);
            }
            $vehicleData['gallery_images'] = $galleryUrls;
        }

        $features = array_filter(array_map('trim', explode(',', $validated['features'] ?? '')));
        $badges = array_filter(array_map('trim', explode(',', $validated['badges'] ?? '')));
        $tags = array_filter(array_map('trim', explode(',', $validated['tags'] ?? '')));
        
        $updatedData = array_merge($vehicleData, [
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'year' => (int) $validated['year'],
            'title' => $validated['brand'] . ' ' . $validated['model'] . ' ' . $validated['year'],
            'price' => $validated['price'],
            'original_price' => $validated['original_price'] ?? null,
            'offer_price' => $validated['offer_price'] ?? null,
            'has_offer' => !empty($validated['offer_price']),
            'offer_expires_at' => $validated['offer_expires_at'] ?? null,
            'status' => $validated['status'] ?? 'available',
            'featured' => isset($validated['featured']),
            'priority' => $validated['priority'] ?? 0,
            'badges' => $badges,
            'mileage' => $validated['mileage'] ?? null,
            'fuel' => $validated['fuel'] ?? null,
            'transmission' => $validated['transmission'] ?? null,
            'engine' => $validated['engine'] ?? null,
            'power' => $validated['power'] ?? null,
            'drivetrain' => $validated['drivetrain'] ?? null,
            'color' => $validated['color'] ?? null,
            'vin' => $validated['vin'] ?? null,
            'condition' => $validated['condition'] ?? null,
            'description' => $validated['description'] ?? null,
            'features' => $features,
            'video_url' => $validated['video_url'] ?? null,
            'location' => $validated['location'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'tags' => $tags,
            'internal_notes' => $validated['internal_notes'] ?? null,
        ]);

        if ($isDbVehicle) {
            $vehicle->update($updatedData);
        } else {
            $items = $this->readStore();
            foreach ($items as $i => $it) {
                if (($it['slug'] ?? '') === $slug) { $items[$i] = $updatedData; break; }
            }
            $this->writeStore($items);
        }

        return redirect()->route('admin.vehicles.index')->with('status', 'Vehicul actualizat cu succes');
    }

    public function destroy(string $slug)
    {
        try {
            Vehicle::query()->where('slug', $slug)->delete();
        } catch (\Throwable $e) {
            $items = $this->readStore();
            $items = array_values(array_filter($items, fn($v) => ($v['slug'] ?? '') !== $slug));
            $this->writeStore($items);
        }
        // delete uploads dir
        $dir = public_path('uploads/vehicles/' . $slug);
        if (File::isDirectory($dir)) { File::deleteDirectory($dir); }
        return redirect()->route('admin.vehicles.index')->with('status', 'Vehicul șters');
    }

    public function show(string $slug)
    {
        $vehicle = Vehicle::query()->where('slug', $slug)->first();
        if (!$vehicle) {
            $items = $this->readStore();
            $vehicle = collect($items)->firstWhere('slug', $slug);
        }
        abort_unless($vehicle, 404);
        return view('admin.vehicles.show', ['vehicle' => is_array($vehicle) ? $vehicle : $vehicle->toArray()]);
    }

    public function toggleFeatured(string $slug)
    {
        $vehicle = Vehicle::query()->where('slug', $slug)->first();
        if ($vehicle) {
            $vehicle->toggleFeatured();
            return response()->json(['success' => true, 'featured' => $vehicle->featured]);
        }
        
        // Fallback for JSON store
        $items = $this->readStore();
        foreach ($items as $i => $item) {
            if (($item['slug'] ?? '') === $slug) {
                $items[$i]['featured'] = !($item['featured'] ?? false);
                $this->writeStore($items);
                return response()->json(['success' => true, 'featured' => $items[$i]['featured']]);
            }
        }
        
        return response()->json(['success' => false], 404);
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $slugs = $request->input('slugs', []);
        
        if (empty($slugs) || !in_array($action, ['feature', 'unfeature', 'available', 'draft', 'delete'])) {
            return response()->json(['success' => false], 400);
        }

        try {
            $vehicles = Vehicle::query()->whereIn('slug', $slugs)->get();
            
            foreach ($vehicles as $vehicle) {
                switch ($action) {
                    case 'feature':
                        $vehicle->update(['featured' => true]);
                        break;
                    case 'unfeature':
                        $vehicle->update(['featured' => false]);
                        break;
                    case 'available':
                        $vehicle->update(['status' => 'available']);
                        break;
                    case 'draft':
                        $vehicle->update(['status' => 'draft']);
                        break;
                    case 'delete':
                        $vehicle->delete();
                        // Delete storage folder
                        $dir = storage_path('app/public/vehicles/' . $vehicle->slug);
                        if (File::isDirectory($dir)) {
                            File::deleteDirectory($dir);
                        }
                        break;
                }
            }
        } catch (\Throwable $e) {
            // Fallback for JSON store
            $items = $this->readStore();
            $updated = false;
            
            foreach ($items as $i => $item) {
                if (in_array($item['slug'] ?? '', $slugs)) {
                    switch ($action) {
                        case 'feature':
                            $items[$i]['featured'] = true;
                            $updated = true;
                            break;
                        case 'unfeature':
                            $items[$i]['featured'] = false;
                            $updated = true;
                            break;
                        case 'available':
                            $items[$i]['status'] = 'available';
                            $updated = true;
                            break;
                        case 'draft':
                            $items[$i]['status'] = 'draft';
                            $updated = true;
                            break;
                        case 'delete':
                            unset($items[$i]);
                            $updated = true;
                            break;
                    }
                }
            }
            
            if ($updated) {
                $this->writeStore(array_values($items));
            }
        }

        return response()->json(['success' => true]);
    }
}


