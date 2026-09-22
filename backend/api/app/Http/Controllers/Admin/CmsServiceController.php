<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmsServiceController extends Controller
{
    /**
     * Menyaring pilihan induk agar menunya tetap dua tingkat dan tidak melingkar:
     * induk harus layanan utama, dan sebuah layanan tidak boleh menjadi induk
     * bagi dirinya sendiri. Layanan yang sudah punya anak juga tidak boleh
     * dijadikan anak, karena cucunya tidak akan muncul di menu.
     */
    private function indukSah($parentId, ?CmsService $service = null): ?int
    {
        if (! $parentId) {
            return null;
        }

        if ($service && ((int) $parentId === $service->id || $service->children()->exists())) {
            return null;
        }

        $induk = CmsService::find($parentId);

        return $induk && $induk->parent_id === null ? $induk->id : null;
    }

    public function index()
    {
        $services = CmsService::utama()->urut()->with('children')->get();

        return view('admin.cms.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.cms.services.create', [
            'induk' => CmsService::calonInduk(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'parent_id' => ['nullable', 'exists:cms_services,id'],
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['parent_id'] = $this->indukSah($data['parent_id'] ?? null);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cms/services', 'public');
        }

        CmsService::create($data);

        return redirect()->route('admin.cms-services.index')->with('success', 'Service created successfully.');
    }

    public function edit(CmsService $service)
    {
        return view('admin.cms.services.edit', [
            'service' => $service,
            'induk'   => CmsService::calonInduk($service),
        ]);
    }

    public function update(Request $request, CmsService $service)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'parent_id' => ['nullable', 'exists:cms_services,id'],
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['parent_id'] = $this->indukSah($data['parent_id'] ?? null, $service);

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
