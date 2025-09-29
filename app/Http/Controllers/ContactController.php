<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'subject' => 'nullable|string|max:100',
            'message' => 'required|string|max:5000',
            'gdpr' => 'accepted',
            'newsletter' => 'nullable|boolean',
        ]);

        ContactMessage::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'gdpr' => (bool) $request->boolean('gdpr'),
            'newsletter' => (bool) $request->boolean('newsletter'),
        ]);

        return back()->with('status', 'Mesaj trimis cu succes!');
    }
}


