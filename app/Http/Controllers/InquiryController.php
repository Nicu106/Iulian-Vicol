<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|integer',
            'vehicle_slug' => 'required|string',
            'vehicle_title' => 'required|string',
            'vehicle_link' => 'required|url',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'message' => 'nullable|string|max:2000',
        ]);

        Inquiry::create($validated + ['status' => 'new']);

        return back()->with('status', 'Solicitarea a fost trimisă. Vă contactăm în curând.');
    }
}


