<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimonial::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $testimonials = $query->orderBy('order')->orderByDesc('created_at')->paginate(20);

        return view('admin.cms.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.cms.testimonials.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'client_photo' => 'nullable|image|max:2048',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:pending,approved,rejected',
            'order' => 'nullable|integer',
            'is_featured' => 'boolean',
        ]);

        if ($request->hasFile('client_photo')) {
            $data['client_photo'] = $request->file('client_photo')->store('testimonials', 'public');
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['submitted_at'] = now();

        Testimonial::create($data);

        return redirect()->route('admin.cms-testimonials.index')->with('status', 'Testimoni berhasil ditambahkan.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.cms.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'client_photo' => 'nullable|image|max:2048',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:pending,approved,rejected',
            'order' => 'nullable|integer',
            'is_featured' => 'boolean',
        ]);

        if ($request->hasFile('client_photo')) {
            $data['client_photo'] = $request->file('client_photo')->store('testimonials', 'public');
        }

        $data['is_featured'] = $request->boolean('is_featured');

        $testimonial->update($data);

        return redirect()->route('admin.cms-testimonials.index')->with('status', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.cms-testimonials.index')->with('status', 'Testimoni berhasil dihapus.');
    }

    public function approve(Testimonial $testimonial)
    {
        $testimonial->update(['status' => 'approved']);

        return back()->with('status', 'Testimoni berhasil disetujui.');
    }

    public function reject(Testimonial $testimonial)
    {
        $testimonial->update(['status' => 'rejected']);

        return back()->with('status', 'Testimoni berhasil ditolak.');
    }

    public function generateLink()
    {
        $testimonial = Testimonial::create([
            'client_name' => '',
            'content' => '',
            'status' => 'pending',
        ]);

        $link = route('testimonial.form', $testimonial->token);

        return back()->with('status', 'Link form testimoni berhasil dibuat: ' . $link)->with('testimonial_link', $link);
    }
}
