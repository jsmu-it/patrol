@extends('layouts.portal')
@section('title', 'Permintaan Kendaraan')

@section('content')
<div class="max-w-2xl mx-auto"
     x-data="{
        bagian: '{{ old('bagian', auth()->user()->profile->division ?? '') }}',
        atasan: {{ Js::from($atasan) }},
     }">

    <div class="mb-5">
        <a href="{{ route('portal.kendaraan.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar Permintaan</a>
        <h1 class="text-xl font-bold text-gray-900 mt-1">Form Permintaan Kendaraan</h1>
        <p class="text-sm text-gray-600">
            Setelah dikirim, permintaan disetujui atasan langsung lalu bagian GA menetapkan driver dan nomor polisinya.
        </p>
    </div>

    <form action="{{ route('portal.kendaraan.store') }}" method="POST">
        @csrf

        <div class="pt-card pt-card-pad mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" value="{{ auth()->user()->name }}" disabled class="pt-input" style="background:#F3F5F8">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bagian <span class="text-red-500">*</span></label>
                    <select name="bagian" required x-model="bagian" class="pt-input">
                        <option value="">&mdash; pilih bagian &mdash;</option>
                        @foreach($daftarDivisi as $d)
                            <option value="{{ $d }}" {{ old('bagian', auth()->user()->profile->division ?? '') === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hari / tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_pakai" required value="{{ old('tanggal_pakai', now()->format('Y-m-d')) }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam <span class="text-red-500">*</span></label>
                    <input type="time" name="jam" required value="{{ old('jam', '08:00') }}" class="pt-input">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan <span class="text-red-500">*</span></label>
                <textarea name="keperluan" rows="5" required class="pt-input"
                          placeholder="Tujuan dan keperluan pemakaian kendaraan.">{{ old('keperluan') }}</textarea>
            </div>
        </div>

        <div class="pt-card pt-card-pad mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Alur persetujuan</div>
            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-700">
                <span class="pt-chip">1. Atasan langsung</span>
                <span x-text="atasan[bagian] || 'pilih bagian dahulu'"></span>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-700 mt-2">
                <span class="pt-chip">2. Bagian GA</span>
                <span>menetapkan driver dan nomor polisi</span>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="pt-btn pt-btn-utama">Kirim Permintaan</button>
            <a href="{{ route('portal.kendaraan.index') }}" class="pt-btn pt-btn-garis">Batal</a>
        </div>
    </form>
</div>
@endsection
