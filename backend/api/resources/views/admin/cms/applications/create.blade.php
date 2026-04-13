@extends('layouts.admin')
@section('page_title', 'Tambah Aplikasi')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-applications.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aplikasi <span class="text-red-500">*</span></label>
            <input type="text" name="name" class="w-full border rounded p-2 @error('name') border-red-500 @enderror" value="{{ old('name') }}" required placeholder="Contoh: JSMU Guard Mobile">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Versi</label>
                <input type="text" name="version" class="w-full border rounded p-2" value="{{ old('version') }}" placeholder="Contoh: 1.0.0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Platform <span class="text-red-500">*</span></label>
                <select name="platform" class="w-full border rounded p-2" required>
                    <option value="android" {{ old('platform') === 'android' ? 'selected' : '' }}>Android</option>
                    <option value="ios" {{ old('platform') === 'ios' ? 'selected' : '' }}>iOS</option>
                    <option value="windows" {{ old('platform') === 'windows' ? 'selected' : '' }}>Windows</option>
                    <option value="macos" {{ old('platform') === 'macos' ? 'selected' : '' }}>macOS</option>
                    <option value="linux" {{ old('platform') === 'linux' ? 'selected' : '' }}>Linux</option>
                </select>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full border rounded p-2" placeholder="Deskripsi singkat tentang aplikasi...">{{ old('description') }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">File Aplikasi <span class="text-red-500">*</span></label>
            <input type="file" name="file" class="w-full border rounded p-2 @error('file') border-red-500 @enderror" required>
            <p class="text-xs text-gray-500 mt-1">Maksimal 100MB. Format: APK, IPA, EXE, DMG, dll.</p>
            @error('file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Icon Aplikasi</label>
            <input type="file" name="icon" class="w-full border rounded p-2" accept="image/*">
            <p class="text-xs text-gray-500 mt-1">Rekomendasi: 512x512px, format PNG/JPG</p>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order (Urutan)</label>
                <input type="number" name="order" value="{{ old('order', 0) }}" class="w-full border rounded p-2">
            </div>
            <div class="flex items-center pt-6">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="mr-2">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif (Tampilkan di website)</label>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.cms-applications.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
