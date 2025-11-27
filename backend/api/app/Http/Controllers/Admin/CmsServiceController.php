<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmsServiceController extends Controller
{
    public function index()
    {
        $services = CmsService::orderBy('order')->get();
        return view('admin.cms.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.cms.services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer',
        ]);

        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cms/services', 'public');
        }

        CmsService::create($data);

        return redirect()->route('admin.cms-services.index')->with('success', 'Service created successfully.');
    }

    public function edit(CmsService $service)
    {
        return view('admin.cms.services.edit', compact('service'));
    }

    public function update(Request $request, CmsService $service)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer',
        ]);

        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('cms/services', 'public');
        }

        $service->update($data);

        return redirect()->route('admin.cms-services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(CmsService $service)
    {
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }
        $service->delete();
        return redirect()->route('admin.cms-services.index')->with('success', 'Service deleted successfully.');
    }
}
