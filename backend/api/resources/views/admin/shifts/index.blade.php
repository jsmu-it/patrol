@extends('layouts.admin')

@section('title', 'Data Shift')
@section('page_title', 'Daftar Shift')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <div class="text-xs text-gray-500">Menampilkan semua shift yang tersedia</div>
            <a href="{{ route('admin.shifts.create') }}" class="px-3 py-1.5 bg-slate-900 text-white rounded text-xs hover:bg-slate-800">
                + Tambah Shift
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Nama Shift</th>
                        <th class="px-4 py-3">Jam Masuk</th>
                        <th class="px-4 py-3">Jam Keluar</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shifts as $shift)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $shift->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $shift->start_time }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $shift->end_time }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.shifts.edit', $shift->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                                <form action="{{ route('admin.shifts.destroy', $shift->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus shift ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada data shift.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shifts->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $shifts->links() }}
            </div>
        @endif
    </div>
@endsection
