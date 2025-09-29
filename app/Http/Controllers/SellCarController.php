<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Vehicle;

class SellCarController extends Controller
{
    public function index()
    {
        return view('pages.sell-car');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'mileage' => 'required|integer|min:0',
            'fuel_type' => 'required|string|in:Benzina,Diésel,Híbrido,Electric',
            'transmission' => 'required|string|in:Manual,Automático',
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

        // Create slug for the vehicle
        $slug = Str::slug($request->brand . ' ' . $request->model . ' ' . $request->year . ' ' . Str::random(6));

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('sell-cars/' . $slug, 'public');
                $imagePaths[] = $path;
            }
        }

        // Create vehicle record
        $vehicle = Vehicle::create([
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
            'slug' => $slug,
            'is_featured' => false,
            'offer_type' => 'Vânzare',
            'seller_name' => $request->seller_name,
            'seller_phone' => $request->seller_phone,
            'seller_email' => $request->seller_email,
            'images' => json_encode($imagePaths),
            'status' => 'pending', // Pending admin approval
        ]);

        return redirect()->route('sell-car')->with('success', 'Anunțul tău a fost trimis cu succes! Va fi verificat de echipa noastră înainte de publicare.');
    }
}

