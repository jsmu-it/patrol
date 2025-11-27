@extends('layouts.admin')
@section('page_title', 'Tambah Penghargaan')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-achievements.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4"><label class="block mb-1">Judul</label><input type="text" name="title" class="w-full border rounded p-2" required></div>
        <div class="mb-4"><label class="block mb-1">Tahun</label><input type="text" name="year" class="w-full border rounded p-2"></div>
        <div class="mb-4"><label class="block mb-1">Deskripsi</label><textarea name="description" id="editor" class="w-full border rounded p-2" rows="3"></textarea></div>
        <div class="mb-4"><label class="block mb-1">Order</label><input type="number" name="order" value="0" class="w-full border rounded p-2"></div>
        <div class="mb-4"><label class="block mb-1">Gambar</label><input type="file" name="image" class="w-full"></div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</div>
<script>
    CKEDITOR.replace('editor');
</script>
@endsection
