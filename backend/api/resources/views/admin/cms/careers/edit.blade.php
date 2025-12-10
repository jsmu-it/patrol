@extends('layouts.admin')
@section('page_title', 'Edit Karir')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-careers.update', $career) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-4"><label class="block mb-1">Posisi</label><input type="text" name="title" value="{{ $career->title }}" class="w-full border rounded p-2" required></div>
        <div class="mb-4"><label class="block mb-1">Lokasi</label><input type="text" name="location" value="{{ $career->location }}" class="w-full border rounded p-2"></div>
        <div class="mb-4">
            <label class="block mb-1">Tipe</label>
            <select name="type" class="w-full border rounded p-2">
                <option value="Full-time" @selected($career->type === 'Full-time')>Full-time</option>
                <option value="Part-time" @selected($career->type === 'Part-time')>Part-time</option>
                <option value="Harian" @selected($career->type === 'Harian')>Harian</option>
            </select>
        </div>
        <div class="mb-4"><label class="block mb-1">Deskripsi</label><textarea name="description" id="editor" class="w-full border rounded p-2" rows="3">{{ $career->description }}</textarea></div>
        <div class="mb-4"><label class="block mb-1">Persyaratan</label><textarea name="requirements" id="editor2" class="w-full border rounded p-2" rows="3">{{ $career->requirements }}</textarea></div>
        <div class="mb-4"><label class="flex items-center"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" {{ $career->is_active ? 'checked' : '' }}> <span class="ml-2">Lowongan Aktif</span></label></div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</div>
<script>
    CKEDITOR.replace('editor');
    CKEDITOR.replace('editor2');
</script>
@endsection
