<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class VehicleController extends Controller
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

    public function show($slug)
    {
        // Try to find in database first
        $vehicle = Vehicle::where('slug', $slug)
            ->whereIn('status', ['available', 'sold'])
            ->first();
            
        // If not found in DB, try JSON store fallback
        if (!$vehicle) {
            $items = $this->readStore();
            $vehicleData = collect($items)->firstWhere('slug', $slug);
            
            if (!$vehicleData) {
                abort(404);
            }
            
            // Convert array to object-like structure for view compatibility
            $vehicle = (object) $vehicleData;
        } else {
            // Increment views for DB vehicles
            $vehicle->incrementViews();
        }
        
        // Get similar vehicles (same brand, different model or same category)
        $similarVehicles = Vehicle::where('status', 'available')
            ->where('slug', '!=', $slug)
            ->where(function($query) use ($vehicle) {
                $brand = is_object($vehicle) ? $vehicle->brand : ($vehicle['brand'] ?? '');
                $bodyType = is_object($vehicle) ? ($vehicle->body_type ?? '') : ($vehicle['body_type'] ?? '');
                $fuel = is_object($vehicle) ? ($vehicle->fuel ?? '') : ($vehicle['fuel'] ?? '');
                
                $query->where('brand', $brand)
                      ->orWhere('body_type', $bodyType)
                      ->orWhere('fuel', $fuel);
            })
            ->limit(4)
            ->get();
            
        return view('pages.vehicle', compact('vehicle', 'similarVehicles'));
    }
}