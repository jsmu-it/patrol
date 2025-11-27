@extends('layouts.admin')
@section('page_title', 'Edit Hero Slide')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-hero-slides.update', $heroSlide) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
            <input type="text" name="title" value="{{ $heroSlide->title }}" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
            <textarea name="subtitle" rows="3" class="w-full border rounded p-2">{{ $heroSlide->subtitle }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Order (Urutan)</label>
            <input type="number" name="order" value="{{ $heroSlide->order }}" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
            @if($heroSlide->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $heroSlide->image) }}" class="h-32 rounded border">
                </div>
            @endif
            <input type="file" name="image" class="w-full">
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.cms-hero-slides.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
