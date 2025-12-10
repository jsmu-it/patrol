@extends('layouts.admin')

@section('title', 'Tambah User Admin')
@section('page_title', 'Tambah User Admin')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.admin-users.store') }}" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 space-y-4">
        @csrf
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm @error('name') border-red-500 @enderror" required>
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm @error('username') border-red-500 @enderror" required>
            @error('username')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 text-sm @error('password') border-red-500 @enderror" required>
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select name="role" class="w-full border border-gray-300 rounded px-3 py-2 text-sm @error('role') border-red-500 @enderror" required>
                <option value="">-- Pilih Role --</option>
                <option value="SUPERADMIN" @selected(old('role') === 'SUPERADMIN')>SUPERADMIN - Akses semua menu</option>
                <option value="ADMIN" @selected(old('role') === 'ADMIN')>ADMIN - Menu Utama, Laporan, Persetujuan, Notifikasi</option>
                <option value="PROJECT_ADMIN" @selected(old('role') === 'PROJECT_ADMIN')>PROJECT_ADMIN - Menu Utama per project</option>
                <option value="HRD" @selected(old('role') === 'HRD')>HRD - Menu HRD (Pelamar, CV, PKWT)</option>
                <option value="PAYROLL" @selected(old('role') === 'PAYROLL')>PAYROLL - Menu Payroll (Slip Gaji)</option>
                <option value="CMS" @selected(old('role') === 'CMS')>CMS - Menu Company Profile & Pengaturan</option>
            </select>
            @error('role')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.admin-users.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded text-sm hover:bg-slate-800">Simpan</button>
        </div>
    </form>
</div>
@endsection
