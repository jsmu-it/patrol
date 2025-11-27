<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmsActivityController extends Controller
{
    public function index()
    {
        $activities = CmsActivity::latest('date')->get();
        return view('admin.cms.activities.index', compact('activities'));
    }

    public function create()
    {
        return view('admin.cms.activities.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'nullable|date',
            'short_description' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'type' => 'required|string',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . uniqid();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cms/activities', 'public');
        }

        CmsActivity::create($data);

        return redirect()->route('admin.cms-activities.index')->with('success', 'Activity created successfully.');
    }

    public function edit(CmsActivity $activity)
    {
        return view('admin.cms.activities.edit', compact('activity'));
    }

    public function update(Request $request, CmsActivity $activity)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'nullable|date',
            'short_description' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'type' => 'required|string',
        ]);

        // Keep slug stable or update? Let's keep it stable usually, or update if title changes.
        // $data['slug'] = Str::slug($data['title']); 

        if ($request->hasFile('image')) {
            if ($activity->image) {
                Storage::disk('public')->delete($activity->image);
            }
            $data['image'] = $request->file('image')->store('cms/activities', 'public');
        }

        $activity->update($data);

        return redirect()->route('admin.cms-activities.index')->with('success', 'Activity updated successfully.');
    }

    public function destroy(CmsActivity $activity)
    {
        if ($activity->image) {
            Storage::disk('public')->delete($activity->image);
        }
        $activity->delete();
        return redirect()->route('admin.cms-activities.index')->with('success', 'Activity deleted successfully.');
    }
}
