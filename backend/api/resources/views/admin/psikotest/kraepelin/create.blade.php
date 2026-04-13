@extends('layouts.admin')

@section('title', 'Buat Soal Kraepelin')
@section('page_title', 'Buat Soal Kraepelin')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.psikotest.kraepelin.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.psikotest.kraepelin.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Set Soal</label>
                <input type="text" name="name" id="name" value="{{ old('name', 'Kraepelin Set ' . date('Y-m-d')) }}" 
                    class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="column_count" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Kolom</label>
                    <input type="number" name="column_count" id="column_count" value="{{ old('column_count', 50) }}" 
                        min="10" max="100" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Standar: 50 kolom</p>
                </div>

                <div>
                    <label for="rows_per_column" class="block text-sm font-medium text-gray-700 mb-1">Baris per Kolom</label>
                    <input type="number" name="rows_per_column" id="rows_per_column" value="{{ old('rows_per_column', 60) }}" 
                        min="20" max="100" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Standar: 60 baris</p>
                </div>

                <div>
                    <label for="time_per_column_seconds" class="block text-sm font-medium text-gray-700 mb-1">Waktu/Kolom (detik)</label>
                    <input type="number" name="time_per_column_seconds" id="time_per_column_seconds" value="{{ old('time_per_column_seconds', 15) }}" 
                        min="5" max="60" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Standar: 15 detik</p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-800 mb-2">💡 Info</h4>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li>• Angka akan di-generate secara random (1-9) saat soal dibuat</li>
                    <li>• Anda bisa regenerate angka kapan saja tanpa mengubah pengaturan</li>
                    <li>• Total waktu test = Jumlah Kolom × Waktu per Kolom</li>
                    <li>• Dengan pengaturan standar: 50 × 15 detik = 12.5 menit</li>
                </ul>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('admin.psikotest.kraepelin.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm">Batal</a>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800 text-sm">Buat Soal</button>
            </div>
        </form>
    </div>
</div>
@endsection
