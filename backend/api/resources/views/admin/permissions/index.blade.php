@extends('layouts.admin')

@section('title', 'Kelola Permission Admin')
@section('page_title', 'Kelola Permission Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-600">Atur admin yang bisa akses web admin dan tentukan otoritas project mereka.</div>
        <a href="{{ route('admin.permissions.create') }}" class="px-4 py-2 rounded-md bg-slate-900 text-white text-sm font-medium hover:bg-slate-800">Tambah Admin</a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Nama</th>
                <th class="px-4 py-3 text-left font-semibold">Email</th>
                <th class="px-4 py-3 text-left font-semibold">Jabatan</th>
                <th class="px-4 py-3 text-left font-semibold">Otoritas Project</th>
                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email}}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($user->role === 'SUPERADMIN') bg-purple-100 text-purple-700
                            @elseif($user->role === 'ADMIN') bg-blue-100 text-blue-700
                            @elseif($user->role === 'PROJECT_ADMIN') bg-cyan-100 text-cyan-700
                            @elseif($user->role === 'HRD') bg-green-100 text-green-700
                            @elseif($user->role === 'PAYROLL') bg-yellow-100 text-yellow-700
                            @elseif($user->role === 'CMS') bg-pink-100 text-pink-700
                            @endif">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($user->role === 'SUPERADMIN')
                            <span class="text-xs text-gray-500 italic">Semua Project</span>
                        @elseif($user->accessibleProjects->count() > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->accessibleProjects as $project)
                                    <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">{{ $project->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.permissions.edit', $user) }}" class="text-sm text-slate-700 hover:underline">Edit</a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.permissions.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus admin ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada data admin.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
@endsection
