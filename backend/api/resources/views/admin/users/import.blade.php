@extends('layouts.admin')

@section('title', 'Import Karyawan')
@section('page_title', 'Import Karyawan')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-xs max-w-xl">
        <p class="mb-3 text-gray-600">1. Download template Excel berikut, lalu isi data karyawan sesuai kolom yang tersedia.</p>
        <a href="{{ route('admin.users.import.template') }}" class="inline-flex items-center px-3 py-1.5 mb-4 rounded bg-slate-900 text-white hover:bg-slate-800">Download template Excel</a>

        <p class="mb-2 text-gray-600">2. Atau jika ingin membuat manual, pastikan header minimal berisi:</p>
        <ul class="list-disc pl-4 mb-3 text-gray-700">
            <li><code>name</code></li>
            <li><code>username</code></li>
            <li><code>email</code></li>
            <li><code>role</code> (ADMIN/GUARD)</li>
            <li><code>project_name</code></li>
            <li><code>password</code></li>
        </ul>
        <p class="mb-4 text-gray-500">Kolom lain seperti <code>nip</code>, <code>ktp_number</code>, alamat, pengalaman, sertifikasi, dll bersifat opsional sesuai kebutuhan. Jika password kosong, akan diisi default <code>password</code>.</p>

        <form method="POST" action="{{ route('admin.users.import.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-gray-600 mb-1">File</label>
                <input type="file" name="file" class="w-full text-xs" required>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-700">Batal</a>
                <button type="submit" class="px-3 py-1.5 rounded bg-slate-900 text-white">Import</button>
            </div>
        </form>
    </div>
@endsection
