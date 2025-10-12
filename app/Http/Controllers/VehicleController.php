<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function show($slug)
    {
        // Allow viewing both available and sold vehicles (for "Vendidos" page)
        $vehicle = Vehicle::where('slug', $slug)
            ->whereIn('status', ['available', 'sold'])
            ->firstOrFail();
            
        // Increment views
        $vehicle->incrementViews();
        
        // Get similar vehicles (same brand, different model or same category)
        $similarVehicles = Vehicle::where('status', 'available')
            ->where('slug', '!=', $slug)
            ->where(function($query) use ($vehicle) {
                $query->where('brand', $vehicle->brand)
                      ->orWhere('body_type', $vehicle->body_type)
                      ->orWhere('fuel', $vehicle->fuel);
            })
            ->limit(4)
            ->get();
            
        return view('pages.vehicle', compact('vehicle', 'similarVehicles'));
    }
}