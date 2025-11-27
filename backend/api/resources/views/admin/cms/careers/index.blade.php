@extends('layouts.admin')
@section('page_title', 'Manajemen Karir')
@section('content')
<div class="mb-4 flex justify-between items-center">
    <p class="text-gray-600">Daftar lowongan kerja.</p>
    <a href="{{ route('admin.cms-careers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">+ Tambah</a>
</div>
<div class="bg-white rounded shadow-sm">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-600 border-b"><tr><th class="px-6 py-3">Posisi</th><th class="px-6 py-3">Lokasi</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Aksi</th></tr></thead>
        <tbody class="divide-y">
            @foreach($careers as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3">{{ $item->title }}</td>
                <td class="px-6 py-3">{{ $item->location }}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-1 rounded text-xs {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $item->is_active ? 'Aktif' : 'Tutup' }}
                    </span>
                </td>
                <td class="px-6 py-3 flex gap-3">
                    <a href="{{ route('admin.cms-careers.edit', $item) }}" class="text-blue-600">Edit</a>
                    <form action="{{ route('admin.cms-careers.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus?');">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
