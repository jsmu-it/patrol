@extends('layouts.admin')
@section('page_title', 'Edit Aplikasi')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-applications.update', $cmsApplication) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aplikasi <span class="text-red-500">*</span></label>
            <input type="text" name="name" class="w-full border rounded p-2 @error('name') border-red-500 @enderror" value="{{ old('name', $cmsApplication->name) }}" required>
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Versi</label>
                <input type="text" name="version" class="w-full border rounded p-2" value="{{ old('version', $cmsApplication->version) }}" placeholder="Contoh: 1.0.0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Platform <span class="text-red-500">*</span></label>
                <select name="platform" class="w-full border rounded p-2" required>
                    <option value="android" {{ old('platform', $cmsApplication->platform) === 'android' ? 'selected' : '' }}>Android</option>
                    <option value="ios" {{ old('platform', $cmsApplication->platform) === 'ios' ? 'selected' : '' }}>iOS</option>
                    <option value="windows" {{ old('platform', $cmsApplication->platform) === 'windows' ? 'selected' : '' }}>Windows</option>
                    <option value="macos" {{ old('platform', $cmsApplication->platform) === 'macos' ? 'selected' : '' }}>macOS</option>
                    <option value="linux" {{ old('platform', $cmsApplication->platform) === 'linux' ? 'selected' : '' }}>Linux</option>
                </select>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full border rounded p-2">{{ old('description', $cmsApplication->description) }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">File Aplikasi</label>
            @if($cmsApplication->file_path)
                <p class="text-sm text-gray-600 mb-2">File saat ini: <span class="font-medium">{{ basename($cmsApplication->file_path) }}</span> ({{ $cmsApplication->file_size_formatted }})</p>
            @endif
            <input type="file" name="file" class="w-full border rounded p-2">
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah file. Maksimal 100MB.</p>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Icon Aplikasi</label>
            @if($cmsApplication->icon)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $cmsApplication->icon) }}" class="h-16 w-16 object-cover rounded">
                </div>
            @endif
            <input type="file" name="icon" class="w-full border rounded p-2" accept="image/*">
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah icon.</p>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order (Urutan)</label>
                <input type="number" name="order" value="{{ old('order', $cmsApplication->order) }}" class="w-full border rounded p-2">
            </div>
            <div class="flex items-center pt-6">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $cmsApplication->is_active) ? 'checked' : '' }} class="mr-2">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif (Tampilkan di website)</label>
            </div>
        </div>
        <div class="mb-4 p-4 bg-gray-50 rounded">
            <p class="text-sm text-gray-600"><strong>Total Downloads:</strong> {{ number_format($cmsApplication->download_count) }}</p>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.cms-applications.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
        </div>
    </form>
</div>
@endsection
