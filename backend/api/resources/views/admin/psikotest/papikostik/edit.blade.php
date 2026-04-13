@extends('layouts.admin')

@section('title', 'Edit Soal Papikostik')
@section('page_title', 'Edit Soal Papikostik')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.psikotest.papikostik.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.psikotest.papikostik.update', $papikostik) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Set Soal</label>
                <input type="text" name="name" id="name" value="{{ old('name', $papikostik->name) }}" 
                    class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ $papikostik->is_active ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
            </div>

            <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                <p class="text-sm text-yellow-800">
                    💡 Untuk mengedit soal secara detail, silakan hapus set ini dan buat ulang dengan soal yang diinginkan.
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('admin.psikotest.papikostik.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm">Batal</a>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800 text-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
