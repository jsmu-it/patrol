@extends('layouts.admin')

@section('page_title', 'Edit Layanan')

@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-services.update', $service) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Layanan</label>
            <input type="text" name="title" value="{{ old('title', $service->title) }}" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Singkat</label>
            <textarea name="short_description" rows="3" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('short_description', $service->short_description) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Lengkap</label>
            <textarea name="full_description" id="editor" rows="6" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('full_description', $service->full_description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan (Order)</label>
                <input type="number" name="order" value="{{ old('order', $service->order) }}" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                 <label class="block text-sm font-medium text-gray-700 mb-1">Icon (Class/Name)</label>
                 <input type="text" name="icon" value="{{ old('icon', $service->icon) }}" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
            @if($service->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $service->image) }}" class="h-32 rounded border">
                </div>
            @endif
            <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.cms-services.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
<script>
    CKEDITOR.replace('editor');
</script>
@endsection
