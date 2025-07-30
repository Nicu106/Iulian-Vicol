<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(Contact::getValidationRules());

        Contact::create($validated);

        return back()->with('success', 'Mesajul tău a fost trimis cu succes! Te vom contacta în curând.');
    }
}
