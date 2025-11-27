<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsCareer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsCareerController extends Controller
{
    public function index()
    {
        $careers = CmsCareer::latest()->get();
        return view('admin.cms.careers.index', compact('careers'));
    }

    public function create()
    {
        return view('admin.cms.careers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . uniqid();

        CmsCareer::create($data);

        return redirect()->route('admin.cms-careers.index')->with('success', 'Job posting created successfully.');
    }

    public function edit(CmsCareer $career)
    {
        return view('admin.cms.careers.edit', compact('career'));
    }

    public function update(Request $request, CmsCareer $career)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $career->update($data);

        return redirect()->route('admin.cms-careers.index')->with('success', 'Job posting updated successfully.');
    }

    public function destroy(CmsCareer $career)
    {
        $career->delete();
        return redirect()->route('admin.cms-careers.index')->with('success', 'Job posting deleted successfully.');
    }
}
