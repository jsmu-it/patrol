@extends('layouts.admin')

@section('title', 'Manajemen User Admin')
@section('page_title', 'Manajemen User Admin')

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-100">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center">
        <h2 class="font-semibold text-gray-800">Daftar User Admin</h2>
        <a href="{{ route('admin.admin-users.create') }}" class="px-4 py-2 bg-slate-900 text-white text-sm rounded hover:bg-slate-800">
            + Tambah User
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Username</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Role</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->username }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @php
                            $roleColors = [
                                'SUPERADMIN' => 'bg-red-100 text-red-800',
                                'ADMIN' => 'bg-blue-100 text-blue-800',
                                'PROJECT_ADMIN' => 'bg-indigo-100 text-indigo-800',
                                'HRD' => 'bg-green-100 text-green-800',
                                'PAYROLL' => 'bg-yellow-100 text-yellow-800',
                                'CMS' => 'bg-purple-100 text-purple-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('admin.admin-users.edit', $user) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.admin-users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada user admin.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="p-4 border-t border-gray-100">
        {{ $users->links() }}
    </div>
    @endif
</div>

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <h3 class="font-semibold text-blue-800 mb-2">Keterangan Role:</h3>
    <ul class="text-sm text-blue-700 space-y-1">
        <li><span class="font-medium">SUPERADMIN</span> - Akses semua menu</li>
        <li><span class="font-medium">ADMIN</span> - Menu Utama, Laporan, Persetujuan, Notifikasi</li>
        <li><span class="font-medium">PROJECT_ADMIN</span> - Menu Utama, Laporan, Persetujuan, Notifikasi (per project)</li>
        <li><span class="font-medium">HRD</span> - Menu HRD (Pelamar, CV, PKWT, Lowongan Kerja)</li>
        <li><span class="font-medium">PAYROLL</span> - Menu Payroll (Slip Gaji)</li>
        <li><span class="font-medium">CMS</span> - Menu Company Profile & Pengaturan</li>
    </ul>
</div>
@endsection
