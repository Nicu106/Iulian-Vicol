<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('order_index')->orderByDesc('created_at')->paginate(20);
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_location' => 'nullable|string|max:255',
            'quote' => 'required|string|max:1000',
            'image' => 'nullable|image|max:4096',
            'order_index' => 'nullable|integer|min:0|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('testimonials', 'public');
            $imagePath = Storage::url($path);
        }

        Testimonial::create([
            'author_name' => $data['author_name'],
            'author_location' => $data['author_location'] ?? null,
            'quote' => $data['quote'],
            'image_path' => $imagePath,
            'order_index' => $data['order_index'] ?? 0,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonio creado.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_location' => 'nullable|string|max:255',
            'quote' => 'required|string|max:1000',
            'image' => 'nullable|image|max:4096',
            'order_index' => 'nullable|integer|min:0|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('testimonials', 'public');
            $testimonial->image_path = Storage::url($path);
        }

        $testimonial->author_name = $data['author_name'];
        $testimonial->author_location = $data['author_location'] ?? null;
        $testimonial->quote = $data['quote'];
        $testimonial->order_index = $data['order_index'] ?? 0;
        $testimonial->is_active = (bool)($data['is_active'] ?? true);
        $testimonial->save();

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonio actualizado.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonio eliminado.');
    }
}


