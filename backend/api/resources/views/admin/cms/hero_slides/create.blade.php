@extends('layouts.admin')
@section('page_title', 'Tambah Hero Slide')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-hero-slides.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
            <input type="text" name="title" class="w-full border rounded p-2" placeholder="Contoh: Professional Security Services">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
            <textarea name="subtitle" rows="3" class="w-full border rounded p-2" placeholder="Contoh: Securing your assets..."></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Order (Urutan)</label>
            <input type="number" name="order" value="0" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Gambar (Wajib)</label>
            <input type="file" name="image" class="w-full" required>
            <p class="text-xs text-gray-500 mt-1">Rekomendasi: 1920x600px</p>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.cms-hero-slides.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
