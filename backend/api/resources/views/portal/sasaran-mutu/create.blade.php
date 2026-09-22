@extends('layouts.portal')
@section('title', 'Buat Laporan Sasaran Mutu')

@section('content')
@php
    // Baris yang dikembalikan setelah validasi gagal, atau satu baris kosong.
    $barisSasaran = old('sasaran', [['sasaran' => '', 'ukuran' => '', 'target_ukuran' => '', 'target_unit' => '', 'realisasi' => '', 'pencapaian' => '']]);
    $barisAksi    = old('tindak_lanjut', [['kegiatan' => '', 'pic' => '', 'batas_waktu' => '', 'status' => 'open']]);
@endphp

<div class="max-w-5xl mx-auto"
     x-data="{
        sasaran: {{ Js::from(array_values($barisSasaran)) }},
        aksi: {{ Js::from(array_values($barisAksi)) }},
        dept: '{{ old('departemen', auth()->user()->profile->division ?? '') }}',
        pemeriksa: {{ Js::from($pemeriksa) }},
        tambahSasaran() { this.sasaran.push({ sasaran:'', ukuran:'', target_ukuran:'', target_unit:'', realisasi:'', pencapaian:'' }) },
        tambahAksi()    { this.aksi.push({ kegiatan:'', pic:'', batas_waktu:'', status:'open' }) },
     }">

    <div class="mb-5">
        <a href="{{ route('portal.sasaran-mutu.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar Sasaran Mutu</a>
        <h1 class="text-xl font-bold text-gray-900 mt-1">Laporan Pencapaian Sasaran Mutu</h1>
        <p class="text-sm text-gray-600">
            Formulir {{ \App\Models\SasaranMutu::KODE_FORMULIR }} rev. {{ \App\Models\SasaranMutu::REVISI }}.
            Setelah dikirim, laporan diperiksa kepala departemen lalu disetujui Direktur,
            dan salinannya masuk ke folder Sasaran Mutu divisi DOC.CONTROL.
        </p>
    </div>

    <form action="{{ route('portal.sasaran-mutu.store') }}" method="POST">
        @csrf

        {{-- Identitas laporan --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bulan <span class="text-red-500">*</span></label>
                    <input type="month" name="bulan" required value="{{ old('bulan', now()->format('Y-m')) }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dept <span class="text-red-500">*</span></label>
                    @php($deptTerpilih = old('departemen', auth()->user()->profile->division ?? ''))
                    <select name="departemen" required x-model="dept" class="pt-input">
                        <option value="">&mdash; pilih departemen &mdash;</option>
                        @foreach($daftarDivisi as $d)
                            <option value="{{ $d }}" {{ $deptTerpilih === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sub Dept</label>
                    <input type="text" name="sub_departemen" value="{{ old('sub_departemen') }}" placeholder="opsional" class="pt-input">
                </div>
            </div>
        </div>

        {{-- PLAN + HASIL --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Plan (Perencanaan) &amp; Hasil</div>
            <p class="text-xs text-gray-500 mb-4">Satu baris untuk satu sasaran: targetnya dicetak di bagian PLAN, realisasinya di bagian HASIL.</p>

            <template x-for="(b, i) in sasaran" :key="i">
                <div class="border border-gray-200 rounded-lg p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-500" x-text="'Sasaran ' + (i + 1)"></span>
                        <button type="button" class="text-xs text-red-600 hover:underline" x-show="sasaran.length > 1"
                                @click="sasaran.splice(i, 1)">Hapus</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sasaran strategi <span class="text-red-500">*</span></label>
                            <input type="text" :name="'sasaran[' + i + '][sasaran]'" x-model="b.sasaran"
                                   placeholder="mis. Meningkatkan kepuasan pelanggan" class="pt-input">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Ukuran strategi</label>
                            <input type="text" :name="'sasaran[' + i + '][ukuran]'" x-model="b.ukuran"
                                   placeholder="mis. Nilai survei kepuasan" class="pt-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Target &mdash; ukuran</label>
                            <input type="text" :name="'sasaran[' + i + '][target_ukuran]'" x-model="b.target_ukuran" placeholder="mis. 90" class="pt-input">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Target &mdash; unit</label>
                            <input type="text" :name="'sasaran[' + i + '][target_unit]'" x-model="b.target_unit" placeholder="mis. %" class="pt-input">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Hasil / realisasi</label>
                            <input type="text" :name="'sasaran[' + i + '][realisasi]'" x-model="b.realisasi" placeholder="mis. 87" class="pt-input">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Pencapaian</label>
                            <input type="text" :name="'sasaran[' + i + '][pencapaian]'" x-model="b.pencapaian" placeholder="mis. 97%" class="pt-input">
                        </div>
                    </div>
                </div>
            </template>

            <button type="button" class="pt-btn pt-btn-garis pt-btn-kecil" @click="tambahSasaran()">+ Tambah sasaran</button>
        </div>

        {{-- DO --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Do (Pelaksanaan) <span class="text-red-500">*</span></div>
            <p class="text-xs text-gray-500 mb-3">Apa saja yang sudah dikerjakan pada periode ini. Satu baris satu kegiatan.</p>
            <textarea name="pelaksanaan" rows="5" required class="pt-input">{{ old('pelaksanaan') }}</textarea>
        </div>

        {{-- CHECK — penyebab --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Check (Pemeriksaan) &mdash; Penyebab</div>
            <p class="text-xs text-gray-500 mb-3">Penyebab bila pencapaian belum sesuai target. Kosongkan bila semua target tercapai.</p>
            <textarea name="penyebab" rows="4" class="pt-input">{{ old('penyebab') }}</textarea>
        </div>

        {{-- ACTION --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Action (Tindak Lanjut)</div>
            <p class="text-xs text-gray-500 mb-4">Rencana perbaikan beserta penanggung jawab dan tenggatnya.</p>

            <template x-for="(a, i) in aksi" :key="i">
                <div class="border border-gray-200 rounded-lg p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-500" x-text="'Kegiatan ' + (i + 1)"></span>
                        <button type="button" class="text-xs text-red-600 hover:underline" x-show="aksi.length > 1"
                                @click="aksi.splice(i, 1)">Hapus</button>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kegiatan</label>
                        <input type="text" :name="'tindak_lanjut[' + i + '][kegiatan]'" x-model="a.kegiatan" class="pt-input">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">PIC</label>
                            <input type="text" :name="'tindak_lanjut[' + i + '][pic]'" x-model="a.pic" class="pt-input">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Batas waktu</label>
                            <input type="text" :name="'tindak_lanjut[' + i + '][batas_waktu]'" x-model="a.batas_waktu" placeholder="mis. 30 September 2026" class="pt-input">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select :name="'tindak_lanjut[' + i + '][status]'" x-model="a.status" class="pt-input">
                                @foreach(\App\Models\SasaranMutu::daftarStatusTindakLanjut() as $n => $l)
                                    <option value="{{ $n }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </template>

            <button type="button" class="pt-btn pt-btn-garis pt-btn-kecil" @click="tambahAksi()">+ Tambah kegiatan</button>
        </div>

        {{-- Tanda tangan --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Pengesahan</div>
            <p class="text-xs text-gray-500 mb-4">Nama yang tercetak pada kolom tanda tangan dokumen.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal dokumen <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" required value="{{ old('tanggal', now()->format('Y-m-d')) }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dibuat oleh</label>
                    <input type="text" value="{{ auth()->user()->name }}" disabled class="pt-input" style="background:#F3F5F8">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan pembuat</label>
                    <input type="text" name="jabatan_pembuat" value="{{ old('jabatan_pembuat', auth()->user()->profile->position ?? '') }}"
                           placeholder="Staff / PIC / Supervisor" class="pt-input">
                </div>
            </div>

            {{-- Nama pemeriksa dan penyetuju tidak diketik: terisi dari akun
                 yang menekan tombol persetujuan nanti. --}}
            <div class="border-t border-gray-100 pt-4 text-sm">
                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Alur persetujuan</div>
                <div class="flex flex-wrap items-center gap-2 text-gray-700">
                    <span class="pt-chip">1. Diperiksa</span>
                    <span x-text="pemeriksa[dept] || 'pilih departemen dahulu'"></span>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-gray-700 mt-2">
                    <span class="pt-chip">2. Disetujui</span>
                    <span>{{ $direktur }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    Keduanya diberi tahu bergiliran, dan namanya tercetak di dokumen setelah menyetujui.
                </p>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="pt-btn pt-btn-utama">Kirim Laporan</button>
            <a href="{{ route('portal.sasaran-mutu.index') }}" class="pt-btn pt-btn-garis">Batal</a>
        </div>
    </form>
</div>
@endsection
