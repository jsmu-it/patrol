@extends('layouts.admin')
@section('page_title', 'Manajemen Aplikasi')
@section('content')
<div class="mb-4 flex justify-between items-center">
    <p class="text-gray-600">Daftar aplikasi yang bisa diunduh.</p>
    <a href="{{ route('admin.cms-applications.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">+ Tambah Aplikasi</a>
</div>
<div class="bg-white rounded shadow-sm overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-600 border-b">
            <tr>
                <th class="px-6 py-3">Nama</th>
                <th class="px-6 py-3">Versi</th>
                <th class="px-6 py-3">Platform</th>
                <th class="px-6 py-3">Ukuran</th>
                <th class="px-6 py-3">Downloads</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Order</th>
                <th class="px-6 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($applications as $app)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3">
                    <div class="flex items-center">
                        @if($app->icon)
                            <img src="{{ asset('storage/' . $app->icon) }}" class="h-10 w-10 object-cover rounded mr-3">
                        @else
                            <div class="h-10 w-10 bg-gray-200 rounded mr-3 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        <span class="font-medium">{{ $app->name }}</span>
                    </div>
                </td>
                <td class="px-6 py-3">{{ $app->version ?? '-' }}</td>
                <td class="px-6 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if($app->platform === 'android') bg-green-100 text-green-800
                        @elseif($app->platform === 'ios') bg-gray-100 text-gray-800
                        @else bg-blue-100 text-blue-800 @endif">
                        {{ ucfirst($app->platform) }}
                    </span>
                </td>
                <td class="px-6 py-3">{{ $app->file_size_formatted }}</td>
                <td class="px-6 py-3">{{ number_format($app->download_count) }}</td>
                <td class="px-6 py-3">
                    @if($app->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Nonaktif</span>
                    @endif
                </td>
                <td class="px-6 py-3">{{ $app->order }}</td>
                <td class="px-6 py-3">
                    <div class="flex gap-3">
                        <a href="{{ route('admin.cms-applications.edit', $app) }}" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                        <form action="{{ route('admin.cms-applications.destroy', $app) }}" method="POST" onsubmit="return confirm('Hapus aplikasi ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">Belum ada aplikasi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
