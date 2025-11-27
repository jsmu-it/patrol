@extends('layouts.admin')

@section('title', 'Laporan Patroli')
@section('page_title', 'Laporan Patroli')

@section('content')
    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4 text-xs grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-gray-600 mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Project</label>
            <select name="project_id" class="w-full border border-gray-300 rounded px-2 py-1.5">
                <option value="">Semua</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string)request('project_id') === (string)$project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Guard</label>
            <select name="user_id" class="w-full border border-gray-300 rounded px-2 py-1.5">
                <option value="">Semua</option>
                @foreach($guards as $guard)
                    <option value="{{ $guard->id }}" @selected((string)request('user_id') === (string)$guard->id)>{{ $guard->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-4 flex flex-wrap justify-between gap-2 items-center mt-2">
            <label class="inline-flex items-center gap-1 text-gray-600">
                <input type="checkbox" name="sort_by_project" value="1" @checked(request('sort_by_project'))>
                <span>Urutkan berdasarkan project</span>
            </label>
            <div class="flex gap-2">
                <button type="submit" class="px-3 py-1.5 rounded bg-gray-800 text-white">Terapkan</button>
            @if($filters)
                <a href="{{ route('admin.reports.patrol.exportExcel', request()->all()) }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-700">Export Excel</a>
                <a href="{{ route('admin.reports.patrol.exportPdf', request()->all()) }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-700">Export PDF</a>
            @endif
            </div>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto text-xs">
        <table class="min-w-full">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-3 py-2 text-left font-semibold">Tanggal</th>
                <th class="px-3 py-2 text-left font-semibold">Jam</th>
                <th class="px-3 py-2 text-left font-semibold">Nama</th>
                <th class="px-3 py-2 text-left font-semibold">Username</th>
                <th class="px-3 py-2 text-left font-semibold">Project</th>
                <th class="px-3 py-2 text-left font-semibold">Checkpoint</th>
                <th class="px-3 py-2 text-left font-semibold">Tipe</th>
                <th class="px-3 py-2 text-left font-semibold">Title</th>
                <th class="px-3 py-2 text-left font-semibold">Pos</th>
                <th class="px-3 py-2 text-left font-semibold">Deskripsi</th>
                <th class="px-3 py-2 text-left font-semibold">Foto</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($records as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">{{ $row['date'] }}</td>
                    <td class="px-3 py-2">{{ $row['time'] }}</td>
                    <td class="px-3 py-2">{{ $row['user'] }}</td>
                    <td class="px-3 py-2">{{ $row['username'] }}</td>
                    <td class="px-3 py-2">{{ $row['project'] }}</td>
                    <td class="px-3 py-2">{{ $row['checkpoint'] }}</td>
                    <td class="px-3 py-2">{{ $row['type'] }}</td>
                    <td class="px-3 py-2">{{ $row['title'] }}</td>
                    <td class="px-3 py-2">{{ $row['post_name'] }}</td>
                    <td class="px-3 py-2">{{ $row['description'] }}</td>
                    <td class="px-3 py-2">
                        @if(!empty($row['photo_url']))
                            <a href="{{ $row['photo_url'] }}" target="_blank" class="text-blue-600 underline">Lihat</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="px-3 py-4 text-center text-gray-500">Belum ada data untuk filter ini.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
