<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsHeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsHeroSlideController extends Controller
{
    public function index()
    {
        $slides = CmsHeroSlide::orderBy('order')->get();
        return view('admin.cms.hero_slides.index', compact('slides'));
    }

    public function create()
    {
        return view('admin.cms.hero_slides.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'image' => 'required|image|max:2048',
            'order' => 'integer',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cms/hero_slides', 'public');
        }

        CmsHeroSlide::create($data);

        return redirect()->route('admin.cms-hero-slides.index')->with('success', 'Slide created successfully.');
    }

    public function edit(CmsHeroSlide $heroSlide)
    {
        return view('admin.cms.hero_slides.edit', compact('heroSlide'));
    }

    public function update(Request $request, CmsHeroSlide $heroSlide)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'order' => 'integer',
        ]);

        if ($request->hasFile('image')) {
            if ($heroSlide->image) {
                Storage::disk('public')->delete($heroSlide->image);
            }
            $data['image'] = $request->file('image')->store('cms/hero_slides', 'public');
        }

        $heroSlide->update($data);

        return redirect()->route('admin.cms-hero-slides.index')->with('success', 'Slide updated successfully.');
    }

    public function destroy(CmsHeroSlide $heroSlide)
    {
        if ($heroSlide->image) {
            Storage::disk('public')->delete($heroSlide->image);
        }
        $heroSlide->delete();
        return redirect()->route('admin.cms-hero-slides.index')->with('success', 'Slide deleted successfully.');
    }
}
