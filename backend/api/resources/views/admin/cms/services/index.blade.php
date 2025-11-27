@extends('layouts.admin')

@section('page_title', 'Manajemen Layanan')

@section('content')
<div class="mb-4 flex justify-between items-center">
    <p class="text-gray-600">Daftar layanan yang ditampilkan di website.</p>
    <a href="{{ route('admin.cms-services.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">+ Tambah Layanan</a>
</div>

<div class="bg-white rounded shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 font-medium border-b">
                <tr>
                    <th class="px-6 py-3">Judul</th>
                    <th class="px-6 py-3">Deskripsi Singkat</th>
                    <th class="px-6 py-3">Order</th>
                    <th class="px-6 py-3">Gambar</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($services as $service)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $service->title }}</td>
                    <td class="px-6 py-3 max-w-xs truncate">{{ $service->short_description }}</td>
                    <td class="px-6 py-3">{{ $service->order }}</td>
                    <td class="px-6 py-3">
                        @if($service->image)
                            <img src="{{ asset('storage/' . $service->image) }}" class="h-10 w-10 object-cover rounded">
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-3 flex gap-3">
                        <a href="{{ route('admin.cms-services.edit', $service) }}" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                        <form action="{{ route('admin.cms-services.destroy', $service) }}" method="POST" onsubmit="return confirm('Hapus layanan ini?');">
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
</div>
@endsection
