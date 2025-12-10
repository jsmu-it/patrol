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
        return redirect()->route('admin.dashboard'); // Not used directly
    }

    public function edit($key)
    {
        $content = CmsContent::firstOrCreate(
            ['key' => $key],
            ['title' => ucwords(str_replace('_', ' ', $key))]
        );

        return view('admin.cms.contents.edit', compact('content', 'key'));
    }

    public function update(Request $request, $key)
    {
        $content = CmsContent::where('key', $key)->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['title', 'body']);

        if ($request->hasFile('image')) {
            if ($content->image) {
                Storage::disk('public')->delete($content->image);
            }
            $data['image'] = $request->file('image')->store('cms/contents', 'public');
        }

        $content->update($data);

        return back()->with('status', 'Konten berhasil diperbarui.');
    }
}
