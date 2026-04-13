@extends('layouts.admin')

@section('title', 'Edit Soal Kraepelin')
@section('page_title', 'Edit Soal Kraepelin')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.psikotest.kraepelin.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.psikotest.kraepelin.update', $kraepelin) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Set Soal</label>
                <input type="text" name="name" id="name" value="{{ old('name', $kraepelin->name) }}" 
                    class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
            </div>

            <div>
                <label for="time_per_column_seconds" class="block text-sm font-medium text-gray-700 mb-1">Waktu per Kolom (detik)</label>
                <input type="number" name="time_per_column_seconds" id="time_per_column_seconds" 
                    value="{{ old('time_per_column_seconds', $kraepelin->time_per_column_seconds) }}" 
                    min="5" max="60" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ $kraepelin->is_active ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-600">
                    <strong>Info:</strong> Jumlah kolom dan baris tidak bisa diubah setelah dibuat. 
                    Untuk mengubahnya, buat soal baru.
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    Kolom: {{ $kraepelin->column_count }} | Baris: {{ $kraepelin->rows_per_column }}
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('admin.psikotest.kraepelin.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm">Batal</a>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800 text-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
