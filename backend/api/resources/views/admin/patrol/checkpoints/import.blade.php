@extends('layouts.admin')

@section('title', 'Import Lokasi Patroli')
@section('page_title', 'Import Lokasi Patroli')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-xs max-w-2xl">
        <div class="mb-4">
            <p class="mb-2 text-gray-600 font-medium">Langkah-langkah:</p>
            <ol class="list-decimal ml-4 space-y-2 text-gray-600">
                <li>Download template Excel berikut:
                    <div class="mt-2">
                        <a href="{{ route('admin.patrol.checkpoints.template') }}" class="inline-flex items-center px-3 py-1.5 rounded bg-slate-900 text-white hover:bg-slate-800">
                            Download Template Excel
                        </a>
                    </div>
                </li>
                <li>Isi data lokasi patroli sesuai kolom yang tersedia. Pastikan kolom <span class="text-red-500 font-bold">project_name</span> sesuai dengan nama project yang ada di sistem.</li>
                <li>Upload file yang sudah diisi melalui form di bawah ini.</li>
            </ol>
        </div>

        <div class="bg-gray-50 p-3 rounded border border-gray-200 mb-6">
            <h3 class="font-semibold text-gray-700 text-xs mb-2">Kolom-kolom yang tersedia:</h3>
            <div class="grid grid-cols-2 gap-2 text-[11px]">
                <div class="flex flex-col">
                    <code class="bg-white px-2 py-1 rounded border mb-1">project_name <span class="text-red-500">*wajib</span></code>
                    <span class="text-gray-500 ml-1">Nama project yang sudah terdaftar</span>
                </div>
                <div class="flex flex-col">
                    <code class="bg-white px-2 py-1 rounded border mb-1">title <span class="text-red-500">*wajib</span></code>
                    <span class="text-gray-500 ml-1">Nama titik patroli (misal: Lobby Utama)</span>
                </div>
                <div class="flex flex-col">
                    <code class="bg-white px-2 py-1 rounded border mb-1">post_name</code>
                    <span class="text-gray-500 ml-1">Nama pos (misal: Pos 1, Barat)</span>
                </div>
                <div class="flex flex-col">
                    <code class="bg-white px-2 py-1 rounded border mb-1">description</code>
                    <span class="text-gray-500 ml-1">Keterangan tambahan lokas</span>
                </div>
                <div class="flex flex-col">
                    <code class="bg-white px-2 py-1 rounded border mb-1">latitude</code>
                    <span class="text-gray-500 ml-1">Koordinat Lintang</span>
                </div>
                <div class="flex flex-col">
                    <code class="bg-white px-2 py-1 rounded border mb-1">longitude</code>
                    <span class="text-gray-500 ml-1">Koordinat Bujur</span>
                </div>
                <div class="flex flex-col">
                    <code class="bg-white px-2 py-1 rounded border mb-1">radius_meters</code>
                    <span class="text-gray-500 ml-1">Radius toleransi (dalam meter), default: 50</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.patrol.checkpoints.import.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-gray-600 mb-1 font-medium">Pilih File (Excel/CSV)</label>
                <input type="file" name="file" class="w-full text-xs p-2 border border-gray-300 rounded" required>
                @error('file')
                    <p class="text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('admin.patrol.checkpoints.index') }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-3 py-1.5 rounded bg-slate-900 text-white hover:bg-slate-800 transition-colors">Proses Import</button>
            </div>
        </form>
    </div>
@endsection
