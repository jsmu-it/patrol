@extends('layouts.admin')
@section('page_title', 'Tambah Aktivitas')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-activities.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4"><label class="block mb-1">Judul</label><input type="text" name="title" class="w-full border rounded p-2" required></div>
        <div class="mb-4"><label class="block mb-1">Tanggal</label><input type="date" name="date" class="w-full border rounded p-2"></div>
        <div class="mb-4"><label class="block mb-1">Tipe</label>
            <select name="type" class="w-full border rounded p-2">
                <option value="internal_event">Internal Event</option>
                <option value="news">News</option>
            </select>
        </div>
        <div class="mb-4"><label class="block mb-1">Deskripsi Singkat</label><textarea name="short_description" class="w-full border rounded p-2" rows="2"></textarea></div>
        <div class="mb-4"><label class="block mb-1">Konten Lengkap</label><textarea name="content" id="editor" class="w-full border rounded p-2" rows="5"></textarea></div>
        <div class="mb-4"><label class="block mb-1">Gambar</label><input type="file" name="image" class="w-full"></div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</div>
<script>
    CKEDITOR.replace('editor');
</script>
@endsection
