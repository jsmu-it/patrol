@extends('layouts.admin')

@section('title', 'Tipe Cuti')
@section('page_title', 'Manajemen Tipe Cuti')

@section('content')
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

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6">
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-800">Tambah Tipe Cuti Baru</h3>
        </div>
        <form action="{{ route('admin.leave-types.store') }}" method="POST" class="p-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tipe Cuti</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Cuti Tahunan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <input type="text" name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Deskripsi singkat">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quota Default (hari)</label>
                    <input type="number" name="default_quota" value="12" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                        Tambah
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Nama</th>
                <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
                <th class="px-4 py-3 text-center font-semibold">Quota Default</th>
                <th class="px-4 py-3 text-center font-semibold">Status</th>
                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($leaveTypes as $type)
                <tr class="hover:bg-gray-50" x-data="{ editing: false }">
                    <td class="px-4 py-3">
                        <span x-show="!editing">{{ $type->name }}</span>
                        <input x-show="editing" type="text" name="name" value="{{ $type->name }}" form="form-{{ $type->id }}" class="px-2 py-1 border border-gray-300 rounded text-sm w-full">
                    </td>
                    <td class="px-4 py-3">
                        <span x-show="!editing">{{ $type->description ?? '-' }}</span>
                        <input x-show="editing" type="text" name="description" value="{{ $type->description }}" form="form-{{ $type->id }}" class="px-2 py-1 border border-gray-300 rounded text-sm w-full">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span x-show="!editing">{{ $type->default_quota }} hari</span>
                        <input x-show="editing" type="number" name="default_quota" value="{{ $type->default_quota }}" min="0" form="form-{{ $type->id }}" class="px-2 py-1 border border-gray-300 rounded text-sm w-20">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span x-show="!editing" class="px-2 py-1 rounded text-xs {{ $type->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $type->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <label x-show="editing" class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" form="form-{{ $type->id }}" {{ $type->is_active ? 'checked' : '' }} class="rounded border-gray-300">
                            <span class="ml-2 text-sm">Aktif</span>
                        </label>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form id="form-{{ $type->id }}" action="{{ route('admin.leave-types.update', $type) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                        </form>
                        <button x-show="!editing" @click="editing = true" class="px-2 py-1 text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                        <button x-show="editing" type="submit" form="form-{{ $type->id }}" class="px-2 py-1 bg-green-600 text-white rounded text-sm">Simpan</button>
                        <button x-show="editing" @click="editing = false" class="px-2 py-1 text-gray-600 hover:text-gray-800 text-sm">Batal</button>
                        <a href="{{ route('admin.leave-types.assign', $type) }}" class="px-2 py-1 text-emerald-600 hover:text-emerald-800 text-sm">Assign</a>
                        @if(!$type->leaveRequests()->exists())
                        <form action="{{ route('admin.leave-types.destroy', $type) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus tipe cuti ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2 py-1 text-red-600 hover:text-red-800 text-sm">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada tipe cuti.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
