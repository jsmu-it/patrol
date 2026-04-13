<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsApplicationController extends Controller
{
    public function index()
    {
        $applications = CmsApplication::orderBy('order')->get();
        return view('admin.cms.applications.index', compact('applications'));
    }

    public function create()
    {
        return view('admin.cms.applications.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'platform' => 'required|in:android,ios,windows,macos,linux',
            'file' => 'required|file|max:102400', // 100MB max
            'icon' => 'nullable|image|max:10240',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $fileName = uniqid() . '_' . time() . '.' . $extension;
            $data['file_path'] = $file->storeAs('cms/applications', $fileName, 'public');
            $data['file_size'] = $file->getSize();
        }

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('cms/applications/icons', 'public');
        }

        $data['is_active'] = $request->has('is_active');

        CmsApplication::create($data);

        return redirect()->route('admin.cms-applications.index')->with('success', 'Application uploaded successfully.');
    }

    public function edit(CmsApplication $cmsApplication)
    {
        return view('admin.cms.applications.edit', compact('cmsApplication'));
    }

    public function update(Request $request, CmsApplication $cmsApplication)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'platform' => 'required|in:android,ios,windows,macos,linux',
            'file' => 'nullable|file|max:102400',
            'icon' => 'nullable|image|max:10240',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        if ($request->hasFile('file')) {
            if ($cmsApplication->file_path) {
                Storage::disk('public')->delete($cmsApplication->file_path);
            }
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $fileName = uniqid() . '_' . time() . '.' . $extension;
            $data['file_path'] = $file->storeAs('cms/applications', $fileName, 'public');
            $data['file_size'] = $file->getSize();
        }

        if ($request->hasFile('icon')) {
            if ($cmsApplication->icon) {
                Storage::disk('public')->delete($cmsApplication->icon);
            }
            $data['icon'] = $request->file('icon')->store('cms/applications/icons', 'public');
        }

        $data['is_active'] = $request->has('is_active');

        $cmsApplication->update($data);

        return redirect()->route('admin.cms-applications.index')->with('success', 'Application updated successfully.');
    }

    public function destroy(CmsApplication $cmsApplication)
    {
        if ($cmsApplication->file_path) {
            Storage::disk('public')->delete($cmsApplication->file_path);
        }
        if ($cmsApplication->icon) {
            Storage::disk('public')->delete($cmsApplication->icon);
        }
        $cmsApplication->delete();

        return redirect()->route('admin.cms-applications.index')->with('success', 'Application deleted successfully.');
    }
}
