@extends('layouts.admin')

@section('title', 'CV Karyawan')
@section('page_title', 'CV Karyawan')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs text-gray-600 mb-1">Cari Nama / NIP</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." class="border border-gray-300 rounded px-3 py-1.5 text-sm w-48">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Filter Project</label>
            <select name="project_id" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                <option value="">Semua Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Urutkan</label>
            <select name="sort" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                <option value="name" @selected(request('sort') == 'name')>Nama</option>
                <option value="project" @selected(request('sort') == 'project')>Project</option>
                <option value="created_at" @selected(request('sort') == 'created_at')>Tanggal Dibuat</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Arah</label>
            <select name="dir" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                <option value="asc" @selected(request('dir', 'asc') == 'asc')>A-Z / Lama</option>
                <option value="desc" @selected(request('dir') == 'desc')>Z-A / Baru</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded text-sm hover:bg-blue-700">Filter</button>
        <a href="{{ route('admin.hrd.cv.index') }}" class="text-gray-600 text-sm hover:underline">Reset</a>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Foto</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">NIP</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Nama</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Jabatan</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Divisi</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Project</th>
                    <th class="px-4 py-2 text-center font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        @if($user->profile && $user->profile->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile->profile_photo_path) }}" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs">N/A</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $user->profile->nip ?? '-' }}</td>
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3">{{ $user->profile->position ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $user->profile->division ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $user->activeProject->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('admin.hrd.cv.show', $user) }}" class="inline-flex items-center gap-1 text-blue-600 hover:underline text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Lihat CV
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada data karyawan dengan profil lengkap.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
