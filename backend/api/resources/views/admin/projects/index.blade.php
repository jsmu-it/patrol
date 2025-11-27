@extends('layouts.admin')

@section('title', 'Project')
@section('page_title', 'Project')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-600">Kelola project dan geofence.</div>
        <a href="{{ route('admin.projects.create') }}" class="px-3 py-2 rounded-md bg-slate-900 text-white text-xs font-medium hover:bg-slate-800">Tambah</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden text-xs">
        <table class="min-w-full">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-3 py-2 text-left font-semibold">Nama</th>
                <th class="px-3 py-2 text-left font-semibold">Client</th>
                <th class="px-3 py-2 text-left font-semibold">Alamat</th>
                <th class="px-3 py-2 text-left font-semibold">Radius (m)</th>
                <th class="px-3 py-2 text-left font-semibold">Aktif</th>
                <th class="px-3 py-2 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($projects as $project)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">{{ $project->name }}</td>
                    <td class="px-3 py-2">{{ $project->client_name }}</td>
                    <td class="px-3 py-2">{{ $project->address }}</td>
                    <td class="px-3 py-2">{{ $project->geofence_radius_meters }}</td>
                    <td class="px-3 py-2">{!! $project->is_active ? '<span class="text-emerald-600 font-semibold">Aktif</span>' : '<span class="text-gray-400">Nonaktif</span>' !!}</td>
                    <td class="px-3 py-2 text-right space-x-2">
                        <a href="{{ route('admin.projects.shifts.edit', $project) }}" class="text-xs text-indigo-600 hover:underline">Shift</a>
                        <a href="{{ route('admin.projects.edit', $project) }}" class="text-xs text-slate-700 hover:underline">Edit</a>
                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirm('Hapus project ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-gray-500">Belum ada data project.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-3 py-2 border-t border-gray-100 text-xs text-gray-500 flex justify-between items-center">
            <div>Menampilkan {{ $projects->firstItem() ?? 0 }}-{{ $projects->lastItem() ?? 0 }} dari {{ $projects->total() }} data</div>
            <div>{{ $projects->links() }}</div>
        </div>
    </div>
@endsection
