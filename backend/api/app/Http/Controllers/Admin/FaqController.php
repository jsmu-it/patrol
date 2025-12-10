<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('order')->orderBy('category')->paginate(20);
        $categories = Faq::distinct()->whereNotNull('category')->pluck('category');

        return view('admin.cms.faqs.index', compact('faqs', 'categories'));
    }

    public function create()
    {
        $categories = Faq::distinct()->whereNotNull('category')->pluck('category');

        return view('admin.cms.faqs.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Faq::create($data);

        return redirect()->route('admin.cms-faqs.index')->with('status', 'FAQ berhasil ditambahkan.');
    }

    public function edit(Faq $faq)
    {
        $categories = Faq::distinct()->whereNotNull('category')->pluck('category');

        return view('admin.cms.faqs.edit', compact('faq', 'categories'));
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $faq->update($data);

        return redirect()->route('admin.cms-faqs.index')->with('status', 'FAQ berhasil diperbarui.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.cms-faqs.index')->with('status', 'FAQ berhasil dihapus.');
    }
}
