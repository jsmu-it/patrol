@extends('layouts.admin')

@section('title', 'Tambah Testimoni')
@section('page_title', 'Tambah Testimoni')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Form Testimoni</h2>
        </div>

        <form method="POST" action="{{ route('admin.cms-testimonials.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Client <span class="text-red-500">*</span></label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <input type="text" name="client_position" value="{{ old('client_position') }}" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Perusahaan</label>
                <input type="text" name="client_company" value="{{ old('client_company') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
                <input type="file" name="client_photo" accept="image/*" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Testimoni <span class="text-red-500">*</span></label>
                <textarea name="content" rows="4" required class="w-full border rounded px-3 py-2">{{ old('content') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating <span class="text-red-500">*</span></label>
                    <select name="rating" required class="w-full border rounded px-3 py-2">
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected(old('rating', 5) == $i)>{{ $i }} Bintang</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="order" value="{{ old('order', 0) }}" class="w-full border rounded px-3 py-2">
                </div>
                <div class="flex items-center pt-6">
                    <input type="checkbox" name="is_featured" value="1" id="is_featured" class="mr-2">
                    <label for="is_featured" class="text-sm">Tampilkan sebagai Featured</label>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
                <a href="{{ route('admin.cms-testimonials.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
