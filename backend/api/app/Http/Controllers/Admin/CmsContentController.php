<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsContentController extends Controller
{
    public function index()
    {
        $contents = CmsContent::all();
        return view('admin.cms.contents.index', compact('contents'));
    }

    public function edit(CmsContent $content)
    {
        return view('admin.cms.contents.edit', compact('content'));
    }

    public function update(Request $request, CmsContent $content)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($content->image) {
                Storage::disk('public')->delete($content->image);
            }
            $data['image'] = $request->file('image')->store('cms/contents', 'public');
        }

        $content->update($data);

        return redirect()->route('admin.cms-contents.index')->with('success', 'Content updated successfully.');
    }
}
