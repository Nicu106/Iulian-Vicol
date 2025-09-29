<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Inquiry;
use App\Models\ContactMessage;
use App\Models\SavedVehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get basic statistics
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('status', 'available')->count();
        $featuredVehicles = Vehicle::where('featured', true)->count();
        $soldVehicles = Vehicle::where('status', 'sold')->count();
        
        // Get inquiry statistics
        $totalInquiries = Inquiry::count();
        $pendingInquiries = Inquiry::where('status', 'pending')->count();
        
        // Get contact message statistics
        $totalMessages = ContactMessage::count();
        $unreadMessages = ContactMessage::where('read_at', null)->count();
        
        // Get sell car statistics
        $pendingSellCars = Vehicle::where('offer_type', 'sell')->where('status', 'pending')->count();
        
        // Monthly statistics
        $monthlyVehicles = Vehicle::whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->count();
        
        $monthlySales = Vehicle::where('status', 'sold')
                              ->whereMonth('updated_at', now()->month)
                              ->whereYear('updated_at', now()->year)
                              ->count();
        
        $monthlyInquiries = Inquiry::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count();
        
        $monthlyViews = Vehicle::sum('views_count') ?? 0;
        
        // Recent activities (you can expand this with actual recent records)
        $recentVehicles = Vehicle::latest()->take(5)->get();
        $recentInquiries = Inquiry::latest()->take(5)->get();
        
        return view('admin.dashboard', compact(
            'totalVehicles',
            'availableVehicles', 
            'featuredVehicles',
            'soldVehicles',
            'totalInquiries',
            'pendingInquiries',
            'totalMessages',
            'unreadMessages',
            'pendingSellCars',
            'monthlyVehicles',
            'monthlySales',
            'monthlyInquiries',
            'monthlyViews',
            'recentVehicles',
            'recentInquiries'
        ));
    }
}
