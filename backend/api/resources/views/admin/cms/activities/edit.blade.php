@extends('layouts.admin')
@section('page_title', 'Edit Aktivitas')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-activities.update', $activity) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="mb-4"><label class="block mb-1">Judul</label><input type="text" name="title" value="{{ $activity->title }}" class="w-full border rounded p-2" required></div>
        <div class="mb-4"><label class="block mb-1">Tanggal</label><input type="date" name="date" value="{{ $activity->date ? $activity->date->format('Y-m-d') : '' }}" class="w-full border rounded p-2"></div>
        <div class="mb-4"><label class="block mb-1">Tipe</label>
            <select name="type" class="w-full border rounded p-2">
                <option value="internal_event" {{ $activity->type == 'internal_event' ? 'selected' : '' }}>Internal Event</option>
                <option value="news" {{ $activity->type == 'news' ? 'selected' : '' }}>News</option>
            </select>
        </div>
        <div class="mb-4"><label class="block mb-1">Deskripsi Singkat</label><textarea name="short_description" class="w-full border rounded p-2" rows="2">{{ $activity->short_description }}</textarea></div>
        <div class="mb-4"><label class="block mb-1">Konten Lengkap</label><textarea name="content" id="editor" class="w-full border rounded p-2" rows="5">{{ $activity->content }}</textarea></div>
        <div class="mb-4"><label class="block mb-1">Gambar</label>@if($activity->image)<img src="{{ asset('storage/'.$activity->image) }}" class="h-20 mb-2">@endif<input type="file" name="image" class="w-full"></div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</div>
<script>
    CKEDITOR.replace('editor');
</script>
@endsection
