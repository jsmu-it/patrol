@extends('layouts.admin')

@section('title', 'Kirim Notifikasi')
@section('page_title', 'Kirim Broadcast Notifikasi')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Form Broadcast Notifikasi</h2>
            <p class="text-sm text-gray-500 mt-1">Kirim notifikasi push ke karyawan melalui aplikasi mobile.</p>
        </div>

        <form method="POST" action="{{ route('admin.broadcast.store') }}" class="p-6 space-y-4" x-data="{ target: 'all' }">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255" class="w-full border rounded px-3 py-2" placeholder="Contoh: Pengumuman Penting">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pesan <span class="text-red-500">*</span></label>
                <textarea name="message" rows="4" required maxlength="1000" class="w-full border rounded px-3 py-2" placeholder="Isi pesan notifikasi...">{{ old('message') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Maksimal 1000 karakter</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Target Penerima <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="target" value="all" x-model="target" class="mr-2" checked>
                        <span>Semua User (yang punya FCM token)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="target" value="project" x-model="target" class="mr-2">
                        <span>Project Tertentu</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="target" value="role" x-model="target" class="mr-2">
                        <span>Role Tertentu</span>
                    </label>
                </div>
            </div>

            <div x-show="target === 'project'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Project</label>
                <select name="target_project_id" class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih Project --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="target === 'role'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Role</label>
                <select name="target_role" class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih Role --</option>
                    <option value="GUARD">GUARD</option>
                    <option value="ADMIN">ADMIN</option>
                    <option value="PROJECT_ADMIN">PROJECT_ADMIN</option>
                </select>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-sm text-yellow-800">
                    <strong>Perhatian:</strong> Notifikasi akan langsung dikirim ke semua penerima yang sesuai kriteria dan memiliki FCM token terdaftar di aplikasi mobile.
                </p>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="return confirm('Kirim notifikasi sekarang?')">
                    Kirim Notifikasi
                </button>
                <a href="{{ route('admin.broadcast.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Batal</a>
            </div>
        </form>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
