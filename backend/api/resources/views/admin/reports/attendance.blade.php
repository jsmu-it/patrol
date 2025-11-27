@extends('layouts.admin')

@section('page_title', 'Laporan Absensi')

@section('content')
<div class="bg-white rounded shadow-sm p-6 mb-6">
    <form action="{{ route('admin.reports.attendance') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ optional($filters['from'] ?? null)->format('Y-m-d') }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ optional($filters['to'] ?? null)->format('Y-m-d') }}" class="w-full border-gray-300 rounded-md shadow-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
            <select name="project_id" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Semua Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ ($filters['project_id'] ?? '') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex-1">Filter</button>
            <a href="{{ route('admin.reports.attendance') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Reset</a>
        </div>
    </form>
</div>

@if(isset($records) && $records->count() > 0)
<div class="bg-white rounded shadow-sm mb-6">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Hasil Laporan</h3>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.attendance.exportExcel', request()->all()) }}" class="px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-700 flex items-center gap-1">
                <span>Excel</span>
            </a>
            <a href="{{ route('admin.reports.attendance.exportPdf', request()->all()) }}" class="px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700 flex items-center gap-1">
                <span>PDF</span>
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 font-medium border-b">
                <tr>
                    <th class="px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Project</th>
                    <th class="px-6 py-3">Shift</th>
                    <th class="px-6 py-3">Masuk</th>
                    <th class="px-6 py-3">Keluar</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($records as $record)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($record['date'])->format('d M Y') }}</td>
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $record['user_name'] }}</td>
                    <td class="px-6 py-3">{{ $record['project_name'] }}</td>
                    <td class="px-6 py-3 text-xs">{{ $record['shift_name'] }}</td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-2">
                            <span class="font-mono">{{ $record['clock_in_time'] }}</span>
                            @if($record['clock_in_photo'])
                                <a href="{{ $record['clock_in_photo'] }}" target="_blank" class="text-blue-500 hover:underline text-xs">[Foto]</a>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-2">
                            <span class="font-mono {{ $record['clock_out_time'] === '-' ? 'text-red-500 font-bold' : '' }}">{{ $record['clock_out_time'] }}</span>
                            @if($record['clock_out_photo'])
                                <a href="{{ $record['clock_out_photo'] }}" target="_blank" class="text-blue-500 hover:underline text-xs">[Foto]</a>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        @if($record['status'] === 'Sesuai Jam Kerja')
                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Sesuai</span>
                        @elseif($record['status'] === 'Lebih Jam Kerja')
                            <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Lebih Jam Kerja</span>
                        @elseif($record['status'] === 'Kurang Jam Kerja')
                            <span class="px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-800">Kurang Jam Kerja</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">{{ $record['status'] }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif(request()->filled('from'))
<div class="bg-white rounded shadow-sm p-8 text-center text-gray-500">
    Tidak ada data absensi ditemukan untuk periode ini.
</div>
@endif
@endsection
