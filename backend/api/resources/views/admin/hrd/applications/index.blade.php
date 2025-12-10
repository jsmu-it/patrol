@extends('layouts.admin')

@section('title', $pageTitle)
@section('page_title', $pageTitle)

@section('content')
    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-600 mb-1">Posisi Dilamar</label>
                <select name="career_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">Semua Posisi</option>
                    @foreach($careers as $career)
                        <option value="{{ $career->id }}" @selected(request('career_id') == $career->id)>{{ $career->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-600 mb-1">Lokasi</label>
                <select name="location" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $location)
                        <option value="{{ $location }}" @selected(request('location') == $location)>{{ $location }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm rounded hover:bg-slate-800">Filter</button>
                <a href="{{ request()->url() }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <div class="text-xs text-gray-500">
                Menampilkan {{ $applications->total() }} {{ strtolower($pageTitle) }}
                @if(request('career_id') || request('location'))
                    (difilter)
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Tanggal Melamar</th>
                        <th class="px-4 py-3">Nama Pelamar</th>
                        <th class="px-4 py-3">Posisi Dilamar</th>
                        <th class="px-4 py-3">Lokasi</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($applications as $app)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $app->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $app->name }}<br>
                                <span class="text-gray-500 font-normal">{{ $app->email }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $app->career ? $app->career->title : 'Umum' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $app->career ? $app->career->location : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-[10px] font-semibold
                                    @if($app->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($app->status === 'interview') bg-blue-100 text-blue-800
                                    @elseif($app->status === 'accepted') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.hrd.applications.show', $app->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Detail</a>
                                <form action="{{ route('admin.hrd.applications.destroy', $app->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus data pelamar ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data {{ strtolower($pageTitle) }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($applications->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
@endsection
