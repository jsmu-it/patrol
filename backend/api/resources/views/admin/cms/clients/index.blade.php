@extends('layouts.admin')
@section('page_title', 'Manajemen Klien')
@section('content')
<div class="mb-4 flex justify-between items-center">
    <p class="text-gray-600">Daftar klien.</p>
    <a href="{{ route('admin.cms-clients.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">+ Tambah</a>
</div>
<div class="bg-white rounded shadow-sm">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-600 border-b"><tr><th class="px-6 py-3">Nama</th><th class="px-6 py-3">Logo</th><th class="px-6 py-3">Order</th><th class="px-6 py-3">Aksi</th></tr></thead>
        <tbody class="divide-y">
            @foreach($clients as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3">{{ $item->name }}</td>
                <td class="px-6 py-3">@if($item->logo)<img src="{{ asset('storage/'.$item->logo) }}" class="h-8">@endif</td>
                <td class="px-6 py-3">{{ $item->order }}</td>
                <td class="px-6 py-3 flex gap-3">
                    <a href="{{ route('admin.cms-clients.edit', $item) }}" class="text-blue-600">Edit</a>
                    <form action="{{ route('admin.cms-clients.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus?');">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
