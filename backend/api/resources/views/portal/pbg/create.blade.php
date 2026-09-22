@extends('layouts.portal')
@section('title', 'Buat PBG')

@section('content')
@php
    $barisBarang = old('barang', [['kode' => '', 'nama' => '', 'jumlah' => '', 'satuan' => '', 'keterangan' => '']]);
@endphp

<div class="max-w-4xl mx-auto"
     x-data="{
        dept: '{{ old('departemen', auth()->user()->profile->division ?? '') }}',
        kabag: {{ Js::from($kabag) }},
        barang: {{ Js::from(array_values($barisBarang)) }},
        tambah() { this.barang.push({ kode:'', nama:'', jumlah:'', satuan:'', keterangan:'' }) },
     }">

    <div class="mb-5">
        <a href="{{ route('portal.pbg.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar PBG</a>
        <h1 class="text-xl font-bold text-gray-900 mt-1">Permintaan Barang Gudang</h1>
        <p class="text-sm text-gray-600">
            Formulir {{ \App\Models\PbgRequest::KODE_FORMULIR }}. Disetujui kepala bagian, lalu diproses gudang di divisi HR &amp; GA.
        </p>
    </div>

    <form action="{{ route('portal.pbg.store') }}" method="POST">
        @csrf

        {{-- Kepala formulir --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama pemohon</label>
                    <input type="text" value="{{ auth()->user()->name }}" disabled class="pt-input" style="background:#F3F5F8">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. PBG</label>
                    <input type="text" value="otomatis saat dikirim" disabled class="pt-input" style="background:#F3F5F8">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departemen <span class="text-red-500">*</span></label>
                    <select name="departemen" required x-model="dept" class="pt-input">
                        <option value="">&mdash; pilih departemen &mdash;</option>
                        @foreach($daftarDivisi as $d)
                            <option value="{{ $d }}" {{ old('departemen', auth()->user()->profile->division ?? '') === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tgl. PBG <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_pbg" required value="{{ old('tgl_pbg', now()->format('Y-m-d')) }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit kerja</label>
                    <input type="text" name="unit_kerja" value="{{ old('unit_kerja') }}" placeholder="mis. Head Office" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tgl. penggunaan</label>
                    <input type="date" name="tgl_penggunaan" value="{{ old('tgl_penggunaan') }}" class="pt-input">
                </div>
            </div>
        </div>

        {{-- Daftar barang --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Barang yang Diminta</div>
            <p class="text-xs text-gray-500 mb-4">Kode boleh dikosongkan bila barangnya belum punya kode gudang.</p>

            <template x-for="(b, i) in barang" :key="i">
                <div class="border border-gray-200 rounded-lg p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-500" x-text="'Barang ' + (i + 1)"></span>
                        <button type="button" class="text-xs text-red-600 hover:underline" x-show="barang.length > 1"
                                @click="barang.splice(i, 1)">Hapus</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Kode</label>
                            <input type="text" :name="'barang[' + i + '][kode]'" x-model="b.kode" class="pt-input">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nama barang dan spesifikasi <span class="text-red-500">*</span></label>
                            <input type="text" :name="'barang[' + i + '][nama]'" x-model="b.nama"
                                   placeholder="mis. Kertas HVS A4 80 gram" class="pt-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah</label>
                            <input type="text" :name="'barang[' + i + '][jumlah]'" x-model="b.jumlah" placeholder="mis. 5" class="pt-input">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                            <input type="text" :name="'barang[' + i + '][satuan]'" x-model="b.satuan" placeholder="mis. rim" class="pt-input">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                            <input type="text" :name="'barang[' + i + '][keterangan]'" x-model="b.keterangan" class="pt-input">
                        </div>
                    </div>
                </div>
            </template>

            <button type="button" class="pt-btn pt-btn-garis pt-btn-kecil" @click="tambah()">+ Tambah barang</button>
        </div>

        <div class="pt-card pt-card-pad mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Alur persetujuan</div>
            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-700">
                <span class="pt-chip">1. Menyetujui</span>
                <span x-text="kabag[dept] || 'pilih departemen dahulu'"></span>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-700 mt-2">
                <span class="pt-chip">2. Gudang</span>
                <span>memproses dan menyerahkan barang</span>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="pt-btn pt-btn-utama">Kirim Permintaan</button>
            <a href="{{ route('portal.pbg.index') }}" class="pt-btn pt-btn-garis">Batal</a>
        </div>
    </form>
</div>
@endsection
