@extends('layouts.admin')
@section('page_title', 'Manajemen Aktivitas')
@section('content')
<div class="mb-4 flex justify-between items-center">
    <div>
        <p class="text-gray-600 mb-2">Daftar aktivitas / event.</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.cms-activities.index') }}" class="px-3 py-1 text-sm rounded {{ !isset($currentType) || !$currentType ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">Semua</a>
            <a href="{{ route('admin.cms-activities.index', ['type' => 'internal_event']) }}" class="px-3 py-1 text-sm rounded {{ isset($currentType) && $currentType == 'internal_event' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">Internal Event</a>
            <a href="{{ route('admin.cms-activities.index', ['type' => 'news']) }}" class="px-3 py-1 text-sm rounded {{ isset($currentType) && $currentType == 'news' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">News</a>
        </div>
    </div>
    <a href="{{ route('admin.cms-activities.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">+ Tambah</a>
</div>
<div class="bg-white rounded shadow-sm">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-600 border-b"><tr><th class="px-6 py-3">Tanggal</th><th class="px-6 py-3">Judul</th><th class="px-6 py-3">Tipe</th><th class="px-6 py-3">Aksi</th></tr></thead>
        <tbody class="divide-y">
            @foreach($activities as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3">{{ $item->date ? $item->date->format('d M Y') : '-' }}</td>
                <td class="px-6 py-3">{{ $item->title }}</td>
                <td class="px-6 py-3">{{ $item->type }}</td>
                <td class="px-6 py-3 flex gap-3">
                    <a href="{{ route('admin.cms-activities.edit', $item) }}" class="text-blue-600">Edit</a>
                    <form action="{{ route('admin.cms-activities.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus?');">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
