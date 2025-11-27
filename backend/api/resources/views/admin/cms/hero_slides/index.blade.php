@extends('layouts.admin')
@section('page_title', 'Manajemen Hero Slider')
@section('content')
<div class="mb-4 flex justify-between items-center">
    <p class="text-gray-600">Daftar slide hero di halaman depan.</p>
    <a href="{{ route('admin.cms-hero-slides.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">+ Tambah Slide</a>
</div>
<div class="bg-white rounded shadow-sm">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-600 border-b"><tr><th class="px-6 py-3">Judul</th><th class="px-6 py-3">Subtitle</th><th class="px-6 py-3">Order</th><th class="px-6 py-3">Gambar</th><th class="px-6 py-3">Aksi</th></tr></thead>
        <tbody class="divide-y">
            @foreach($slides as $slide)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3">{{ $slide->title }}</td>
                <td class="px-6 py-3 max-w-xs truncate">{{ $slide->subtitle }}</td>
                <td class="px-6 py-3">{{ $slide->order }}</td>
                <td class="px-6 py-3">
                    @if($slide->image)
                        <img src="{{ asset('storage/' . $slide->image) }}" class="h-10 w-20 object-cover rounded">
                    @else
                        -
                    @endif
                </td>
                <td class="px-6 py-3 flex gap-3">
                    <a href="{{ route('admin.cms-hero-slides.edit', $slide) }}" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                    <form action="{{ route('admin.cms-hero-slides.destroy', $slide) }}" method="POST" onsubmit="return confirm('Hapus slide ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
