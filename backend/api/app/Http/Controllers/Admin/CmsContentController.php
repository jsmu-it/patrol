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
        // Barisnya disiapkan dari katalog supaya blok yang belum pernah diisi
        // tetap muncul — dulu menu ini melempar balik ke dashboard, jadi HSSE
        // dan Archipelago tidak pernah kelihatan bisa diedit.
        foreach (CmsContent::BLOK as $key => [$label, $letak]) {
            CmsContent::firstOrCreate(['key' => $key], ['title' => $label]);
        }

        $contents = CmsContent::orderByRaw('FIELD(`key`, ?, ?, ?, ?, ?)', array_keys(CmsContent::BLOK))
            ->get();

        return view('admin.cms.contents.index', compact('contents'));
    }

    public function edit($key)
    {
        abort_unless(array_key_exists($key, CmsContent::BLOK), 404);

        $content = CmsContent::firstOrCreate(
            ['key' => $key],
            ['title' => CmsContent::BLOK[$key][0]]
        );

        return view('admin.cms.contents.edit', compact('content', 'key'));
    }

    public function update(Request $request, $key)
    {
        abort_unless(array_key_exists($key, CmsContent::BLOK), 404);

        $content = CmsContent::where('key', $key)->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
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
