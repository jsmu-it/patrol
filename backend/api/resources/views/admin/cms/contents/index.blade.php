@extends('layouts.admin')

@section('page_title', 'Manajemen Konten Halaman')

@section('content')
<div class="bg-white rounded shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 font-medium border-b">
                <tr>
                    <th class="px-6 py-3">Key</th>
                    <th class="px-6 py-3">Judul / Keterangan</th>
                    <th class="px-6 py-3">Preview</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($contents as $content)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $content->key }}</td>
                    <td class="px-6 py-3">
                        @if($content->title)
                            <div class="font-semibold">{{ $content->title }}</div>
                        @endif
                        @if($content->subtitle)
                            <div class="text-gray-500">{{ $content->subtitle }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        @if($content->image)
                            <img src="{{ asset('storage/' . $content->image) }}" class="h-10 w-auto rounded">
                        @elseif($content->body)
                            <span class="text-gray-400 italic">HTML Content</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        <a href="{{ route('admin.cms-contents.edit', $content) }}" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
