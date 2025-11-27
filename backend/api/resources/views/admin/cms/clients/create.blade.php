@extends('layouts.admin')
@section('page_title', 'Tambah Klien')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-clients.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4"><label class="block mb-1">Nama Klien</label><input type="text" name="name" class="w-full border rounded p-2" required></div>
        <div class="mb-4"><label class="block mb-1">Website</label><input type="url" name="website" class="w-full border rounded p-2"></div>
        <div class="mb-4"><label class="block mb-1">Order</label><input type="number" name="order" value="0" class="w-full border rounded p-2"></div>
        <div class="mb-4"><label class="block mb-1">Logo</label><input type="file" name="logo" class="w-full" required></div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</div>
@endsection
