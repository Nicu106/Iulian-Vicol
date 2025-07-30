<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vehicle::with('primaryImage')->available();

        // Filter by brand
        if ($request->filled('brand')) {
            $query->byBrand($request->brand);
        }

        // Filter by price range
        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->byPriceRange($request->min_price, $request->max_price);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by fuel type
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        // Filter by transmission
        if ($request->filled('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        $vehicles = $query->latest()->paginate(12);

        // Get unique brands for filter
        $brands = Vehicle::available()->distinct()->pluck('brand')->sort();

        return view('vehicles.index', compact('vehicles', 'brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load('images');
        
        // Get related vehicles
        $relatedVehicles = Vehicle::with('primaryImage')
            ->where('id', '!=', $vehicle->id)
            ->where('brand', $vehicle->brand)
            ->available()
            ->take(4)
            ->get();

        return view('vehicles.show', compact('vehicle', 'relatedVehicles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get vehicles for AJAX requests (filtering)
     */
    public function getVehicles(Request $request)
    {
        $query = Vehicle::with('primaryImage')->available();

        if ($request->filled('brand')) {
            $query->byBrand($request->brand);
        }

        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->byPriceRange($request->min_price, $request->max_price);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $vehicles = $query->latest()->paginate(12);

        return response()->json([
            'vehicles' => $vehicles,
            'html' => view('components.vehicle-grid', compact('vehicles'))->render()
        ]);
    }
}
