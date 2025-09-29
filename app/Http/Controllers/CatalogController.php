<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();
        
        // Status filter (default to available)
        $status = $request->get('status');
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'available');
        }
        
        // Search functionality
        $q = trim((string) $request->get('q'));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('brand', 'like', "%$q%")
                    ->orWhere('model', 'like', "%$q%")
                    ->orWhere('title', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%");
            });
        }
        
        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand', $request->get('brand'));
        }
        
        // Filter by model
        if ($request->filled('model')) {
            $query->where('model', 'like', '%' . $request->get('model') . '%');
        }
        
        // Filter by price range
        if ($request->filled('price_min')) {
            $priceMin = (float) $request->get('price_min');
            $query->where(function ($sub) use ($priceMin) {
                $sub->where('price', '>=', $priceMin)
                    ->orWhere('offer_price', '>=', $priceMin);
            });
        }
        
        if ($request->filled('price_max')) {
            $priceMax = (float) $request->get('price_max');
            $query->where(function ($sub) use ($priceMax) {
                $sub->where('price', '<=', $priceMax)
                    ->orWhere('offer_price', '<=', $priceMax);
            });
        }
        
        // Filter by year range
        if ($request->filled('year_min')) {
            $query->where('year', '>=', $request->get('year_min'));
        }
        
        if ($request->filled('year_max')) {
            $query->where('year', '<=', $request->get('year_max'));
        }
        
        // Filter by mileage range
        if ($request->filled('mileage_min')) {
            $query->where('mileage', '>=', $request->get('mileage_min'));
        }
        
        if ($request->filled('mileage_max')) {
            $query->where('mileage', '<=', $request->get('mileage_max'));
        }
        
        // Filter by featured
        if ($request->filled('featured')) {
            $query->where('featured', (bool) $request->get('featured'));
        }

        // Filter by fuel type
        if ($request->filled('fuel') && $request->get('fuel') !== 'Toate') {
            $query->where(function($sub) use ($request) {
                $sub->where('fuel', $request->get('fuel'))
                    ->orWhere('fuel_type', $request->get('fuel'));
            });
        }
        
        // Filter by transmission
        if ($request->filled('transmission') && $request->get('transmission') !== 'Toate') {
            $query->where('transmission', $request->get('transmission'));
        }
        
        // Filter by body type
        if ($request->filled('body_type') && $request->get('body_type') !== 'Toate') {
            $bodyType = $request->get('body_type');
            $query->where(function ($sub) use ($bodyType) {
                $sub->where('body_type', $bodyType)
                    ->orWhere('description', 'like', "%$bodyType%")
                    ->orWhereJsonContains('features', $bodyType);
            });
        }
        
        // Filter by color
        if ($request->filled('color') && $request->get('color') !== 'Toate') {
            $query->where('color', $request->get('color'));
        }
        
        // Filter by keywords
        if ($request->filled('keywords')) {
            $keywords = $request->get('keywords');
            $query->where(function ($sub) use ($keywords) {
                $sub->where('description', 'like', "%$keywords%")
                    ->orWhereJsonContains('features', $keywords);
            });
        }
        
        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(offer_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(offer_price, price) DESC');
                break;
            case 'year_desc':
                $query->orderBy('year', 'desc');
                break;
            case 'year_asc':
                $query->orderBy('year', 'asc');
                break;
            case 'mileage_asc':
                $query->orderBy('mileage', 'asc');
                break;
            case 'mileage_desc':
                $query->orderBy('mileage', 'desc');
                break;
            case 'featured':
                $query->orderBy('featured', 'desc')->orderBy('created_at', 'desc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
        }
        
        // Paginate results
        $vehicles = $query->paginate(12)->withQueryString();
        
        // Get unique brands for filter dropdown
        $brands = Vehicle::where('status', 'available')
            ->distinct()
            ->pluck('brand')
            ->filter()
            ->sort()
            ->values();
        
        // Get unique fuel types for filter dropdown
        $fuelTypes = Vehicle::where('status', 'available')
            ->selectRaw('COALESCE(fuel, fuel_type) as fuel_type')
            ->distinct()
            ->pluck('fuel_type')
            ->filter()
            ->sort()
            ->values();
        
        // Get unique transmissions for filter dropdown
        $transmissions = Vehicle::where('status', 'available')
            ->distinct()
            ->pluck('transmission')
            ->filter()
            ->sort()
            ->values();
        
        // Get unique colors for filter dropdown
        $colors = Vehicle::where('status', 'available')
            ->distinct()
            ->pluck('color')
            ->filter()
            ->sort()
            ->values();
        
        // Get unique body types for filter dropdown
        $bodyTypes = Vehicle::where('status', 'available')
            ->whereNotNull('body_type')
            ->distinct()
            ->pluck('body_type')
            ->filter()
            ->sort()
            ->values();
        
        return view('pages.catalog', compact(
            'vehicles',
            'brands',
            'fuelTypes',
            'transmissions',
            'colors',
            'bodyTypes',
            'q'
        ));
    }
}
