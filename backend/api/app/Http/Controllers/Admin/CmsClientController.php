<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsClientController extends Controller
{
    public function index()
    {
        $clients = CmsClient::orderBy('order')->get();
        return view('admin.cms.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.cms.clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|url',
            'logo' => 'required|image|max:2048',
            'order' => 'integer',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('cms/clients', 'public');
        }

        CmsClient::create($data);

        return redirect()->route('admin.cms-clients.index')->with('success', 'Client created successfully.');
    }

    public function edit(CmsClient $client)
    {
        return view('admin.cms.clients.edit', compact('client'));
    }

    public function update(Request $request, CmsClient $client)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|max:2048',
            'order' => 'integer',
        ]);

        if ($request->hasFile('logo')) {
            if ($client->logo) {
                Storage::disk('public')->delete($client->logo);
            }
            $data['logo'] = $request->file('logo')->store('cms/clients', 'public');
        }

        $client->update($data);

        return redirect()->route('admin.cms-clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(CmsClient $client)
    {
        if ($client->logo) {
            Storage::disk('public')->delete($client->logo);
        }
        $client->delete();
        return redirect()->route('admin.cms-clients.index')->with('success', 'Client deleted successfully.');
    }
}
