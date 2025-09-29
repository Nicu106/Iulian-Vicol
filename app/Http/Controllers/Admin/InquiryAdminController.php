<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\ContactMessage;

class InquiryAdminController extends Controller
{
    public function index()
    {
        $inquiries = Inquiry::orderByDesc('created_at')->paginate(20);
        $contactMessages = ContactMessage::orderByDesc('created_at')->paginate(20);
        return view('admin.inquiries.index', compact('inquiries', 'contactMessages'));
    }
}


