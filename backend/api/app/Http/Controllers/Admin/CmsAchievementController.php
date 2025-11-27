<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsAchievementController extends Controller
{
    public function index()
    {
        $achievements = CmsAchievement::orderBy('order')->get();
        return view('admin.cms.achievements.index', compact('achievements'));
    }

    public function create()
    {
        return view('admin.cms.achievements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'nullable|string|max:4',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'order' => 'integer',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cms/achievements', 'public');
        }

        CmsAchievement::create($data);

        return redirect()->route('admin.cms-achievements.index')->with('success', 'Achievement created successfully.');
    }

    public function edit(CmsAchievement $achievement)
    {
        return view('admin.cms.achievements.edit', compact('achievement'));
    }

    public function update(Request $request, CmsAchievement $achievement)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'nullable|string|max:4',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'order' => 'integer',
        ]);

        if ($request->hasFile('image')) {
            if ($achievement->image) {
                Storage::disk('public')->delete($achievement->image);
            }
            $data['image'] = $request->file('image')->store('cms/achievements', 'public');
        }

        $achievement->update($data);

        return redirect()->route('admin.cms-achievements.index')->with('success', 'Achievement updated successfully.');
    }

    public function destroy(CmsAchievement $achievement)
    {
        if ($achievement->image) {
            Storage::disk('public')->delete($achievement->image);
        }
        $achievement->delete();
        return redirect()->route('admin.cms-achievements.index')->with('success', 'Achievement deleted successfully.');
    }
}
