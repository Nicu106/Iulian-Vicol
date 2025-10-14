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
        $priceRange = $request->get('price_range');
        $hasOffers = $request->get('has_offers');
        
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

            if ($hasOffers !== null && $hasOffers !== '') {
                if ($hasOffers == '1') {
                    $query->where('has_offer', true)
                          ->where(function($q) {
                              $q->whereNull('offer_expires_at')
                                ->orWhere('offer_expires_at', '>=', now());
                          });
                } else {
                    $query->where(function($q) {
                        $q->where('has_offer', false)
                          ->orWhere('offer_expires_at', '<', now());
                    });
                }
            }

            // Apply price range filter
            if ($priceRange) {
                $prices = explode('-', $priceRange);
                if (count($prices) === 2) {
                    $minPrice = (float) $prices[0];
                    $maxPrice = (float) $prices[1];
                    $query->where(function($q) use ($minPrice, $maxPrice) {
                        $q->whereBetween('price', [$minPrice, $maxPrice])
                          ->orWhereBetween('offer_price', [$minPrice, $maxPrice]);
                    });
                }
            }
            
            // Apply sorting
            switch ($sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'offer_price_asc':
                    $query->orderBy('offer_price', 'asc');
                    break;
                case 'offer_price_desc':
                    $query->orderBy('offer_price', 'desc');
                    break;
                case 'discount_desc':
                    $query->orderByRaw('((original_price - offer_price) / original_price * 100) DESC');
                    break;
                case 'views':
                    $query->orderByDesc('views_count');
                    break;
                case 'priority':
                    $query->orderByDesc('priority');
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

            // Apply price range filter to JSON store
            if ($priceRange) {
                $prices = explode('-', $priceRange);
                if (count($prices) === 2) {
                    $minPrice = (float) $prices[0];
                    $maxPrice = (float) $prices[1];
                    $items = array_values(array_filter($items, function ($v) use ($minPrice, $maxPrice) {
                        $price = $v['price'] ?? 0;
                        $offerPrice = $v['offer_price'] ?? $price;
                        return ($price >= $minPrice && $price <= $maxPrice) || 
                               ($offerPrice >= $minPrice && $offerPrice <= $maxPrice);
                    }));
                }
            }

            // Apply has offers filter to JSON store
            if ($hasOffers !== null && $hasOffers !== '') {
                if ($hasOffers == '1') {
                    $items = array_values(array_filter($items, function ($v) {
                        return !empty($v['offer_price']) && 
                               (empty($v['offer_expires_at']) || $v['offer_expires_at'] >= date('Y-m-d'));
                    }));
                } else {
                    $items = array_values(array_filter($items, function ($v) {
                        return empty($v['offer_price']) || 
                               (!empty($v['offer_expires_at']) && $v['offer_expires_at'] < date('Y-m-d'));
                    }));
                }
            }

            // Apply sorting to JSON store
            switch ($sort) {
                case 'price_asc':
                    usort($items, fn($a, $b) => ($a['price'] ?? 0) <=> ($b['price'] ?? 0));
                    break;
                case 'price_desc':
                    usort($items, fn($a, $b) => ($b['price'] ?? 0) <=> ($a['price'] ?? 0));
                    break;
                case 'offer_price_asc':
                    usort($items, fn($a, $b) => ($a['offer_price'] ?? $a['price'] ?? 0) <=> ($b['offer_price'] ?? $b['price'] ?? 0));
                    break;
                case 'offer_price_desc':
                    usort($items, fn($a, $b) => ($b['offer_price'] ?? $b['price'] ?? 0) <=> ($a['offer_price'] ?? $a['price'] ?? 0));
                    break;
                case 'priority':
                    usort($items, fn($a, $b) => ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0));
                    break;
                default:
                    usort($items, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
            }
        }

        // Get pricing statistics
        $pricingStats = $this->getPricingStatistics($items);
        
        return view('admin.vehicles.index', compact('items', 'q', 'status', 'featured', 'sort', 'priceRange', 'hasOffers', 'pricingStats'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

    public function store(Request $request)
    {
        // Force PHP settings for this request
        ini_set('upload_max_filesize', '0');
        ini_set('post_max_size', '0');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '0');
        ini_set('max_file_uploads', '200');
        
        // DEBUG: Detailed logging for creation
        \Log::info('STORE DEBUG START', [
            'has_files' => !empty($request->allFiles()),
            'all_files_keys' => array_keys($request->allFiles()),
            'cover_image_hasfile' => $request->hasFile('cover_image'),
            'cover_image_file' => $request->file('cover_image') ? 'EXISTS' : 'NULL',
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'php_upload_max' => ini_get('upload_max_filesize'),
            'php_post_max' => ini_get('post_max_size')
        ]);
        
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:150',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'offer_expires_at' => 'nullable|date|after_or_equal:today',
            'offer_type' => 'nullable|in:flash_sale,seasonal,clearance,negotiable,promotion',
            'offer_description' => 'nullable|string|max:500',
            'status' => 'nullable|in:available,reserved,sold,maintenance,draft,clearance',
            'featured' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:999',
            'badges' => 'nullable|string',
            'mileage' => 'nullable|integer|min:0',
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

        // Additional validation for offer fields
        if (!empty($validated['offer_price']) && empty($validated['original_price'])) {
            $validated['original_price'] = $validated['price'];
        }
        
        if (!empty($validated['offer_price']) && $validated['offer_price'] >= $validated['price']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['offer_price' => 'Prețul ofertei trebuie să fie mai mic decât prețul normal.']);
        }

        $slug = Str::slug($validated['brand'] . ' ' . $validated['model'] . ' ' . $validated['year'] . ' ' . Str::random(5));

        // Store uploads in storage/app/public/vehicles/{slug}
        $coverUrl = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            \Log::info('FILE DEBUG', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'is_valid' => $file->isValid(),
                'error' => $file->getError()
            ]);
            
            try {
                $path = $file->store('vehicles/' . $slug, 'public');
                $coverUrl = Storage::url($path);
                \Log::info('SUCCESS: Cover image uploaded to: ' . $path);
            } catch (\Exception $e) {
                \Log::error('FAILED to store image: ' . $e->getMessage());
                $coverUrl = null;
            }
        } else {
            \Log::info('NO COVER IMAGE - hasFile returned false');
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
            'price' => (float) $validated['price'],
            'original_price' => !empty($validated['original_price']) ? (float) $validated['original_price'] : null,
            'offer_price' => !empty($validated['offer_price']) ? (float) $validated['offer_price'] : null,
            'has_offer' => !empty($validated['offer_price']),
            'offer_expires_at' => $validated['offer_expires_at'] ?? null,
            'offer_type' => $validated['offer_type'] ?? null,
            'offer_description' => $validated['offer_description'] ?? null,
            'pricing_history' => [],
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
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
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
            ->with('status', '¡Vehículo añadido con éxito! Slug: ' . $slug)
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
        // Force PHP settings for this request
        ini_set('upload_max_filesize', '0');
        ini_set('post_max_size', '0');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '0');
        ini_set('max_file_uploads', '200');
        
        // Removed excessive debug logging for performance
        
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:150',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'offer_expires_at' => 'nullable|date|after_or_equal:today',
            'offer_type' => 'nullable|in:flash_sale,seasonal,clearance,negotiable,promotion',
            'offer_description' => 'nullable|string|max:500',
            'status' => 'nullable|in:available,reserved,sold,maintenance,draft,clearance',
            'featured' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:999',
            'badges' => 'nullable|string',
            'mileage' => 'nullable|integer|min:0',
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

        // Additional validation for offer fields
        if (!empty($validated['offer_price']) && empty($validated['original_price'])) {
            $validated['original_price'] = $validated['price'];
        }
        
        if (!empty($validated['offer_price']) && $validated['offer_price'] >= $validated['price']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['offer_price' => 'Prețul ofertei trebuie să fie mai mic decât prețul normal.']);
        }

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
            $file = $request->file('cover_image');
            // File upload debug removed for performance
            
            try {
                $path = $file->store('vehicles/' . $slug, 'public');
                $vehicleData['cover_image'] = Storage::url($path);
                // Success logging removed for performance
            } catch (\Exception $e) {
                \Log::error('FAILED to update image: ' . $e->getMessage());
            }
        }

        if ($request->hasFile('gallery_images')) {
            $galleryUrls = $vehicleData['gallery_images'] ?? [];
            foreach ($request->file('gallery_images') as $img) {
                try {
                    $path = $img->store('vehicles/' . $slug, 'public');
                    $galleryUrls[] = Storage::url($path);
                } catch (\Throwable $e) {
                    \Log::error('Gallery image store failed: ' . $e->getMessage());
                }
            }
            // Ensure unique and keep insertion order
            $vehicleData['gallery_images'] = array_values(array_unique($galleryUrls));
        }

        // Handle removal of existing gallery images
        if ($request->filled('removed_gallery_images')) {
            $toRemove = json_decode($request->input('removed_gallery_images'), true);
            if (is_array($toRemove) && !empty($toRemove)) {
                $current = $vehicleData['gallery_images'] ?? [];
                $vehicleData['gallery_images'] = array_values(array_filter($current, function ($url) use ($toRemove) {
                    return !in_array($url, $toRemove, true);
                }));
                // Optionally remove files from storage if they are ours
                foreach ($toRemove as $url) {
                    $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
                    if ($path) {
                        try { Storage::disk('public')->delete($path); } catch (\Throwable $e) { /* ignore */ }
                    }
                }
            }
        }

        $features = array_filter(array_map('trim', explode(',', $validated['features'] ?? '')));
        $badges = array_filter(array_map('trim', explode(',', $validated['badges'] ?? '')));
        $tags = array_filter(array_map('trim', explode(',', $validated['tags'] ?? '')));
        
        $updatedData = array_merge($vehicleData, [
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'year' => (int) $validated['year'],
            'title' => $validated['brand'] . ' ' . $validated['model'] . ' ' . $validated['year'],
            'price' => (float) $validated['price'],
            'original_price' => !empty($validated['original_price']) ? (float) $validated['original_price'] : null,
            'offer_price' => !empty($validated['offer_price']) ? (float) $validated['offer_price'] : null,
            'has_offer' => !empty($validated['offer_price']),
            'offer_expires_at' => $validated['offer_expires_at'] ?? null,
            'offer_type' => $validated['offer_type'] ?? null,
            'offer_description' => $validated['offer_description'] ?? null,
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
            'updated_at' => now()->toISOString(),
        ]);

        if ($isDbVehicle) {
            $vehicle->update($updatedData);
        } else {
            $items = $this->readStore();
            foreach ($items as $i => $it) {
                if (($it['slug'] ?? '') === $slug) { 
                    $items[$i] = $updatedData; 
                    break; 
                }
            }
            $this->writeStore($items);
        }

        // Allow explicit redirect target from form (e.g., back to list)
        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))
                ->with('status', 'Vehículo actualizado con éxito');
        }

        return redirect()->route('admin.vehicles.index')->with('status', 'Vehículo actualizado con éxito');
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

    public function bulkPricingUpdate(Request $request)
    {
        $request->validate([
            'action' => 'required|in:percentage_increase,percentage_decrease,fixed_increase,fixed_decrease,set_offer,remove_offers,set_original_price',
            'slugs' => 'required|array|min:1',
            'slugs.*' => 'string',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'amount' => 'nullable|numeric|min:0',
            'offer_percentage' => 'nullable|numeric|min:0|max:100',
            'offer_amount' => 'nullable|numeric|min:0',
            'offer_expires_at' => 'nullable|date|after_or_equal:today',
        ]);

        $action = $request->input('action');
        $slugs = $request->input('slugs');
        $updated = 0;

        try {
            $vehicles = Vehicle::query()->whereIn('slug', $slugs)->get();
            
            foreach ($vehicles as $vehicle) {
                $this->applyPricingAction($vehicle, $action, $request);
                $updated++;
            }
        } catch (\Throwable $e) {
            // Fallback for JSON store
            $items = $this->readStore();
            $updated = 0;
            
            foreach ($items as $i => $item) {
                if (in_array($item['slug'] ?? '', $slugs)) {
                    $this->applyPricingActionToArray($items[$i], $action, $request);
                    $updated = true;
                }
            }
            
            if ($updated) {
                $this->writeStore($items);
            }
        }

        return response()->json([
            'success' => true, 
            'message' => "Actualizat $updated vehicule",
            'updated_count' => $updated
        ]);
    }

    private function applyPricingAction($vehicle, $action, $request)
    {
        $currentPrice = $vehicle->price;
        $newPrice = $currentPrice;

        switch ($action) {
            case 'percentage_increase':
                $percentage = $request->input('percentage', 0);
                $newPrice = $currentPrice * (1 + $percentage / 100);
                break;
                
            case 'percentage_decrease':
                $percentage = $request->input('percentage', 0);
                $newPrice = $currentPrice * (1 - $percentage / 100);
                break;
                
            case 'fixed_increase':
                $amount = $request->input('amount', 0);
                $newPrice = $currentPrice + $amount;
                break;
                
            case 'fixed_decrease':
                $amount = $request->input('amount', 0);
                $newPrice = max(0, $currentPrice - $amount);
                break;
                
            case 'set_offer':
                if ($request->input('offer_percentage')) {
                    $percentage = $request->input('offer_percentage');
                    $offerPrice = $currentPrice * (1 - $percentage / 100);
                } elseif ($request->input('offer_amount')) {
                    $offerPrice = max(0, $currentPrice - $request->input('offer_amount'));
                } else {
                    $offerPrice = $currentPrice * 0.9; // Default 10% discount
                }
                
                $vehicle->update([
                    'original_price' => $currentPrice,
                    'offer_price' => round($offerPrice, 2),
                    'has_offer' => true,
                    'offer_expires_at' => $request->input('offer_expires_at'),
                ]);
                return;
                
            case 'remove_offers':
                $vehicle->update([
                    'offer_price' => null,
                    'has_offer' => false,
                    'offer_expires_at' => null,
                ]);
                return;
                
            case 'set_original_price':
                $vehicle->update([
                    'original_price' => $request->input('amount', $currentPrice),
                ]);
                return;
        }

        if ($newPrice !== $currentPrice) {
            $vehicle->update(['price' => round($newPrice, 2)]);
        }
    }

    private function applyPricingActionToArray(&$item, $action, $request)
    {
        $currentPrice = $item['price'] ?? 0;
        $newPrice = $currentPrice;

        switch ($action) {
            case 'percentage_increase':
                $percentage = $request->input('percentage', 0);
                $newPrice = $currentPrice * (1 + $percentage / 100);
                break;
                
            case 'percentage_decrease':
                $percentage = $request->input('percentage', 0);
                $newPrice = $currentPrice * (1 - $percentage / 100);
                break;
                
            case 'fixed_increase':
                $amount = $request->input('amount', 0);
                $newPrice = $currentPrice + $amount;
                break;
                
            case 'fixed_decrease':
                $amount = $request->input('amount', 0);
                $newPrice = max(0, $currentPrice - $amount);
                break;
                
            case 'set_offer':
                if ($request->input('offer_percentage')) {
                    $percentage = $request->input('offer_percentage');
                    $offerPrice = $currentPrice * (1 - $percentage / 100);
                } elseif ($request->input('offer_amount')) {
                    $offerPrice = max(0, $currentPrice - $request->input('offer_amount'));
                } else {
                    $offerPrice = $currentPrice * 0.9; // Default 10% discount
                }
                
                $item['original_price'] = $currentPrice;
                $item['offer_price'] = round($offerPrice, 2);
                $item['has_offer'] = true;
                $item['offer_expires_at'] = $request->input('offer_expires_at');
                return;
                
            case 'remove_offers':
                $item['offer_price'] = null;
                $item['has_offer'] = false;
                $item['offer_expires_at'] = null;
                return;
                
            case 'set_original_price':
                $item['original_price'] = $request->input('amount', $currentPrice);
                return;
        }

        if ($newPrice !== $currentPrice) {
            $item['price'] = round($newPrice, 2);
        }
    }

    public function getPricingStatistics($items)
    {
        $stats = [
            'total_vehicles' => count($items),
            'with_offers' => 0,
            'active_offers' => 0,
            'expired_offers' => 0,
            'avg_price' => 0,
            'avg_offer_price' => 0,
            'total_value' => 0,
            'total_offer_value' => 0,
            'price_ranges' => [
                '0-10000' => 0,
                '10000-25000' => 0,
                '25000-50000' => 0,
                '50000-100000' => 0,
                '100000+' => 0,
            ]
        ];

        if (empty($items)) {
            return $stats;
        }

        $totalPrice = 0;
        $totalOfferPrice = 0;
        $offerCount = 0;
        $activeOfferCount = 0;

        foreach ($items as $item) {
            $price = $item['price'] ?? 0;
            $totalPrice += $price;
            $totalValue = $price;

            // Count price ranges
            if ($price <= 10000) $stats['price_ranges']['0-10000']++;
            elseif ($price <= 25000) $stats['price_ranges']['10000-25000']++;
            elseif ($price <= 50000) $stats['price_ranges']['25000-50000']++;
            elseif ($price <= 100000) $stats['price_ranges']['50000-100000']++;
            else $stats['price_ranges']['100000+']++;

            // Handle offers
            if (!empty($item['offer_price'])) {
                $stats['with_offers']++;
                $offerCount++;
                $totalOfferPrice += $item['offer_price'];
                $totalValue = $item['offer_price'];

                // Check if offer is active
                if (empty($item['offer_expires_at']) || $item['offer_expires_at'] >= date('Y-m-d')) {
                    $stats['active_offers']++;
                    $activeOfferCount++;
                } else {
                    $stats['expired_offers']++;
                }
            }

            $stats['total_value'] += $totalValue;
        }

        $stats['avg_price'] = $totalPrice / count($items);
        $stats['avg_offer_price'] = $offerCount > 0 ? $totalOfferPrice / $offerCount : 0;
        $stats['total_offer_value'] = $totalOfferPrice;

        return $stats;
    }

    public function exportPricingReport(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,json,xlsx',
            'filters' => 'nullable|array'
        ]);

        try {
            $query = Vehicle::query();
            
            // Apply filters if provided
            if ($request->has('filters')) {
                $filters = $request->input('filters');
                
                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                
                if (!empty($filters['has_offers'])) {
                    if ($filters['has_offers'] == '1') {
                        $query->where('has_offer', true);
                    } else {
                        $query->where('has_offer', false);
                    }
                }
                
                if (!empty($filters['price_min'])) {
                    $query->where('price', '>=', $filters['price_min']);
                }
                
                if (!empty($filters['price_max'])) {
                    $query->where('price', '<=', $filters['price_max']);
                }
            }
            
            $vehicles = $query->get();
            
            $data = $vehicles->map(function($vehicle) {
                return [
                    'slug' => $vehicle->slug,
                    'title' => $vehicle->title,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                    'price' => $vehicle->price,
                    'original_price' => $vehicle->original_price,
                    'offer_price' => $vehicle->offer_price,
                    'has_offer' => $vehicle->has_offer ? 'Da' : 'Nu',
                    'offer_expires_at' => $vehicle->offer_expires_at,
                    'discount_percentage' => $vehicle->discount_percentage,
                    'status' => $vehicle->status,
                    'featured' => $vehicle->featured ? 'Da' : 'Nu',
                    'views_count' => $vehicle->views_count,
                    'inquiries_count' => $vehicle->inquiries_count,
                    'created_at' => $vehicle->created_at,
                    'updated_at' => $vehicle->updated_at,
                ];
            });
            
            $format = $request->input('format');
            
            if ($format === 'json') {
                return response()->json($data);
            } elseif ($format === 'csv') {
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="pricing_report_' . date('Y-m-d') . '.csv"',
                ];
                
                $callback = function() use ($data) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, array_keys($data->first()));
                    
                    foreach ($data as $row) {
                        fputcsv($file, $row);
                    }
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            }
            
            return response()->json(['error' => 'Format not supported'], 400);
            
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
    }
}


