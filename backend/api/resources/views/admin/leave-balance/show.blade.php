@extends('layouts.admin')

@section('title', 'Detail Saldo Cuti')
@section('page_title', 'Saldo Cuti: {{ $user->name }}')

@section('content')
    @unless($berhak ?? true)
        <div class="mb-4 px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm">
            Penempatan karyawan ini bukan Head Office, jadi tidak mendapat jatah cuti.
            Di aplikasi tampil sebagai &ldquo;tidak ada cuti&rdquo;.
        </div>
    @endunless
    <div class="mb-4">
        <a href="{{ route('admin.leave-balance.index', ['year' => $year]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
            &larr; Kembali ke Daftar
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Informasi Karyawan</h3>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Nama:</span>
                <span class="font-medium ml-2">{{ $user->name }}</span>
            </div>
            <div>
                <span class="text-gray-500">NIP:</span>
                <span class="font-medium ml-2">{{ $user->profile?->nip ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Tahun:</span>
                <span class="font-medium ml-2">{{ $year }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Tipe Cuti</th>
                <th class="px-4 py-3 text-center font-semibold">Quota</th>
                <th class="px-4 py-3 text-center font-semibold">Terpakai</th>
                <th class="px-4 py-3 text-center font-semibold">Sisa</th>
                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @foreach($leaveTypes as $lt)
                @php
                    $baris = $saldo[$lt->id] ?? null;
                    $quota = $baris['quota'] ?? 0;
                    $used = $baris['used'] ?? 0;
                    $remaining = $baris['remaining'] ?? 0;
                    $tersimpan = $baris['tersimpan'] ?? false;
                @endphp
                <tr class="hover:bg-gray-50" x-data="{ editing: false }">
                    <td class="px-4 py-3 font-medium">{{ $lt->name }}</td>
                    <td class="px-4 py-3 text-center">
                        <span x-show="!editing">
                            {{ $quota }}
                            @unless($tersimpan)<span class="text-[10px] text-gray-400">(belum disetel)</span>@endunless
                        </span>
                        <input x-show="editing" type="number" name="quota" value="{{ $quota }}" min="0" form="form-{{ $lt->id }}" class="px-2 py-1 border border-gray-300 rounded text-sm w-20 text-center">
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $used }}</td>
                    <td class="px-4 py-3 text-center {{ $remaining > 0 ? 'text-green-600 font-medium' : 'text-gray-400' }}">{{ $remaining }}</td>
                    <td class="px-4 py-3 text-right">
                        <form id="form-{{ $lt->id }}" action="{{ route('admin.leave-balance.update', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="leave_type_id" value="{{ $lt->id }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                        </form>
                        <button x-show="!editing" @click="editing = true" class="px-2 py-1 text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                        <button x-show="editing" type="submit" form="form-{{ $lt->id }}" class="px-2 py-1 bg-green-600 text-white rounded text-sm">Simpan</button>
                        <button x-show="editing" @click="editing = false" class="px-2 py-1 text-gray-600 hover:text-gray-800 text-sm">Batal</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
