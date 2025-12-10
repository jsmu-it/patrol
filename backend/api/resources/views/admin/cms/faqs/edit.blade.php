@extends('layouts.admin')

@section('title', 'Edit FAQ')
@section('page_title', 'Edit FAQ')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Edit FAQ</h2>
        </div>

        <form method="POST" action="{{ route('admin.cms-faqs.update', $faq) }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan <span class="text-red-500">*</span></label>
                <input type="text" name="question" value="{{ old('question', $faq->question) }}" required class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jawaban <span class="text-red-500">*</span></label>
                <textarea name="answer" rows="5" required class="w-full border rounded px-3 py-2">{{ old('answer', $faq->answer) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <input type="text" name="category" value="{{ old('category', $faq->category) }}" list="categories" class="w-full border rounded px-3 py-2" placeholder="Umum, Layanan, dll">
                    <datalist id="categories">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="order" value="{{ old('order', $faq->order) }}" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_active" value="1" id="is_active" class="mr-2" @checked($faq->is_active)>
                <label for="is_active" class="text-sm">Aktif</label>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
                <a href="{{ route('admin.cms-faqs.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
