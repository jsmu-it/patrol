@extends('layouts.admin')

@section('page_title', 'Edit Konten: ' . $content->key)

@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-contents.update', $content) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul (Title)</label>
            <input type="text" name="title" value="{{ old('title', $content->title) }}" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle / Ringkasan</label>
            <textarea name="subtitle" rows="2" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('subtitle', $content->subtitle) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Konten Utama (Body)</label>
            <textarea name="body" id="editor" rows="10" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('body', $content->body) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Gambar (Opsional)</label>
            @if($content->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $content->image) }}" class="h-32 rounded border">
                </div>
            @endif
            <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.cms-contents.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
    CKEDITOR.replace('editor');
</script>
@endsection
