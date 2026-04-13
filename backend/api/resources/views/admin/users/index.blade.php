@extends('layouts.admin')

@section('title', 'Karyawan')
@section('page_title', 'Karyawan')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-600">Kelola akun admin dan guard.</div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.export', request()->query()) }}" class="px-3 py-2 rounded-md border border-green-600 text-xs font-medium text-green-600 hover:bg-green-50">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('admin.users.import.form') }}" class="px-3 py-2 rounded-md border border-gray-300 text-xs font-medium text-gray-700 hover:bg-gray-50">Import</a>
            <a href="{{ route('admin.users.create') }}" class="px-3 py-2 rounded-md bg-slate-900 text-white text-xs font-medium hover:bg-slate-800">Tambah</a>
        </div>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end text-xs">
        <div>
            <label class="block text-gray-600 mb-1">Role</label>
            <select name="role" class="border border-gray-300 rounded px-2 py-1 text-xs">
                <option value="">Semua</option>
                <option value="ADMIN" @selected(request('role') === 'ADMIN')>ADMIN</option>
                <option value="GUARD" @selected(request('role') === 'GUARD')>GUARD</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Project</label>
            <select name="project_id" class="border border-gray-300 rounded px-2 py-1 text-xs">
                <option value="">Semua</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string)request('project_id') === (string)$project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Cari Nama / Username</label>
            <input type="text" name="search" value="{{ request('search') }}" class="border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Ketik nama atau username...">
        </div>
        <label class="inline-flex items-center gap-1 mt-4 text-gray-600">
            <input type="checkbox" name="sort_by_project" value="1" @checked(request('sort_by_project'))>
            <span>Urutkan berdasarkan project</span>
        </label>
        <button type="submit" class="px-3 py-1.5 rounded bg-gray-800 text-white">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full text-xs">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-3 py-2 text-left font-semibold">Nama</th>
                <th class="px-3 py-2 text-left font-semibold">Username</th>
                <th class="px-3 py-2 text-left font-semibold">Role</th>
                <th class="px-3 py-2 text-left font-semibold">Project Aktif</th>
                <th class="px-3 py-2 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">{{ $user->name }}</td>
                    <td class="px-3 py-2">{{ $user->username }}</td>
                    <td class="px-3 py-2">{{ $user->role }}</td>
                    <td class="px-3 py-2">{{ $user->activeProject?->name ?? '-' }}</td>
                    <td class="px-3 py-2 text-right space-x-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-slate-700 hover:underline">Edit</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus karyawan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-3 py-4 text-center text-gray-500">Belum ada data karyawan.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-3 py-2 border-t border-gray-100 text-xs text-gray-500 flex justify-between items-center">
            <div>Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data</div>
            <div>{{ $users->links() }}</div>
        </div>
    </div>
@endsection
