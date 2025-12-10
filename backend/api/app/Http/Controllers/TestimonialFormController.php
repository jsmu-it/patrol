<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialFormController extends Controller
{
    public function showForm(string $token)
    {
        $testimonial = Testimonial::where('token', $token)->firstOrFail();

        // Jika sudah disubmit, tampilkan pesan terima kasih
        if ($testimonial->submitted_at && $testimonial->client_name) {
            return view('testimonial.submitted', compact('testimonial'));
        }

        return view('testimonial.form', compact('testimonial'));
    }

    public function submitForm(Request $request, string $token)
    {
        $testimonial = Testimonial::where('token', $token)->firstOrFail();

        $data = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'client_photo' => 'nullable|image|max:2048',
            'content' => 'required|string|min:20',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if ($request->hasFile('client_photo')) {
            $data['client_photo'] = $request->file('client_photo')->store('testimonials', 'public');
        }

        $data['submitted_at'] = now();
        $data['status'] = 'pending';

        $testimonial->update($data);

        return redirect()->route('testimonial.form', $token)->with('success', 'Terima kasih! Testimoni Anda telah berhasil dikirim.');
    }
}
