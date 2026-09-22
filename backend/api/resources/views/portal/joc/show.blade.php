@extends('layouts.portal')
@section('title', 'Kartu ' . $kartu->nomor)

@section('content')
@php
    $hse = (auth()->user()->profile->division ?? null) === 'HSE'
        || in_array(auth()->user()->role, ['SUPERADMIN','HRD']);
    $w = ['baru'=>'pt-i-merah','diproses'=>'pt-i-kuning','selesai'=>'pt-i-hijau'][$kartu->status];
@endphp

<div class="max-w-3xl mx-auto">
    <a href="{{ route('portal.joc.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar kartu</a>

    <div class="flex flex-wrap items-center gap-2 mt-2 mb-5">
        <h1 class="text-xl font-bold text-gray-900">{{ $kartu->nomor }}</h1>
        <span class="pt-lencana">{{ $kartu->labelJenis() }}</span>
        <span class="text-xs px-2 py-0.5 rounded-full {{ $w }}">{{ \App\Models\JocCard::daftarStatus()[$kartu->status] }}</span>
    </div>

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">Data Pengamatan</div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
            <div><dt class="text-gray-500 text-xs">Tanggal & jam</dt><dd class="text-gray-900">{{ $kartu->tanggal->format('d F Y') }} &middot; {{ substr($kartu->jam, 0, 5) }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Lokasi</dt><dd class="text-gray-900">{{ $kartu->lokasi }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Project</dt><dd class="text-gray-900">{{ $kartu->project->name ?? '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Pengamat</dt><dd class="text-gray-900">{{ $kartu->nama }}</dd></div>
        </dl>
    </div>

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">Kategori Temuan</div>
        <div class="flex flex-wrap gap-2">
            @forelse($kartu->labelKategori() as $k)
                <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">{{ $k }}</span>
            @empty
                <span class="text-sm text-gray-500">—</span>
            @endforelse
        </div>
        @if($kartu->kategori_lain)
            <p class="text-sm text-gray-700 mt-3">Lain-lain: {{ $kartu->kategori_lain }}</p>
        @endif
    </div>

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">Rincian</div>
        <div class="mb-4">
            <div class="text-xs text-gray-500 mb-1">Temuan pengamatan</div>
            <p class="text-sm text-gray-900 whitespace-pre-line">{{ $kartu->rincian_temuan }}</p>
        </div>
        <div class="mb-4">
            <div class="text-xs text-gray-500 mb-1">Tindakan / rekomendasi</div>
            <p class="text-sm text-gray-900 whitespace-pre-line">{{ $kartu->rincian_tindakan }}</p>
        </div>
        @if($kartu->catatan)
            <div>
                <div class="text-xs text-gray-500 mb-1">Catatan tambahan</div>
                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $kartu->catatan }}</p>
            </div>
        @endif
    </div>

    @if(!empty($kartu->foto))
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-3">Foto Bukti</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($kartu->foto as $i => $f)
                    <a href="{{ route('portal.joc.foto', [$kartu, $i]) }}" target="_blank" rel="noopener" class="block">
                        <img src="{{ route('portal.joc.foto', [$kartu, $i]) }}" alt="Foto bukti {{ $i + 1 }}"
                             class="w-full rounded-lg border border-gray-200" style="max-height:280px;object-fit:cover">
                    </a>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 mt-2">Klik foto untuk melihat ukuran penuh.</p>
        </div>
    @endif

    @if($kartu->ttd)
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-2">Tanda tangan pengamat</div>
            <img src="{{ $kartu->ttd }}" alt="Tanda tangan" class="border border-gray-200 rounded-lg bg-white" style="max-height:130px">
        </div>
    @endif

    {{-- Tanggapan HSE --}}
    <div class="pt-card pt-card-pad">
        <div class="pt-judul mb-3">Tindak Lanjut HSE</div>

        @if($kartu->tanggapan_hse)
            <p class="text-sm text-gray-900 whitespace-pre-line mb-2">{{ $kartu->tanggapan_hse }}</p>
            <p class="text-xs text-gray-500">
                Ditangani {{ optional($kartu->ditangani_pada)->diffForHumans() }}
            </p>
        @elseif(! $hse)
            <p class="text-sm text-gray-500">Belum ada tanggapan dari divisi HSE.</p>
        @endif

        @if($hse)
            <form action="{{ route('portal.joc.tanggapi', $kartu) }}" method="POST" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="pt-input">
                        @foreach(\App\Models\JocCard::daftarStatus() as $nilai => $label)
                            <option value="{{ $nilai }}" {{ $kartu->status === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggapan</label>
                    <textarea name="tanggapan_hse" rows="3" class="pt-input">{{ old('tanggapan_hse', $kartu->tanggapan_hse) }}</textarea>
                </div>
                <button type="submit" class="pt-btn pt-btn-utama pt-btn-kecil">Simpan Tanggapan</button>
            </form>
        @endif
    </div>
</div>
@endsection
