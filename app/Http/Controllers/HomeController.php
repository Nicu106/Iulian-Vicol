<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredVehicles = Vehicle::with('primaryImage')
            ->available()
            ->latest()
            ->take(6)
            ->get();

        $latestVehicles = Vehicle::with('primaryImage')
            ->available()
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('featuredVehicles', 'latestVehicles'));
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function faq()
    {
        return view('faq');
    }

    public function terms()
    {
        return view('terms');
    }
}
