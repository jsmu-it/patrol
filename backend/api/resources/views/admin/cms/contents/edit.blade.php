@extends('layouts.admin')

@section('page_title', 'Edit Konten: ' . ucwords(str_replace('_', ' ', $key)))

@section('content')
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.cms-contents.update', $key) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" name="title" value="{{ old('title', $content->title) }}" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Isi Konten</label>
                <textarea name="body" id="editor" rows="10" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">{{ old('body', $content->body) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar (Opsional)</label>
                @if($content->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $content->image) }}" alt="Current Image" class="h-32 object-cover rounded">
                    </div>
                @endif
                <input type="file" name="image" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        CKEDITOR.replace('editor');
    </script>
@endpush
