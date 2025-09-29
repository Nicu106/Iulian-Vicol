<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Suggested vehicles for categories based on simple heuristics
        $suvs = Vehicle::query()
            ->where('status', 'available')
            ->where(function ($q) {
                $q->where('body_type', 'like', '%SUV%')
                  ->orWhere('description', 'like', '%SUV%')
                  ->orWhereJsonContains('features', 'SUV');
            })
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $sedans = Vehicle::query()
            ->where('status', 'available')
            ->where(function ($q) {
                $q->where('body_type', 'like', '%Sedan%')
                  ->orWhere('description', 'like', '%sedan%')
                  ->orWhereJsonContains('features', 'sedan');
            })
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $evs = Vehicle::query()
            ->where('status', 'available')
            ->where(function ($q) {
                $q->where('fuel', 'Electric')
                  ->orWhere('fuel_type', 'Electric')
                  ->orWhere('fuel', 'Hibrid')
                  ->orWhere('fuel_type', 'Hibrid')
                  ->orWhere('description', 'like', '%electric%')
                  ->orWhere('description', 'like', '%hibrid%');
            })
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        return view('pages.home', compact('suvs', 'sedans', 'evs'));
    }
}
