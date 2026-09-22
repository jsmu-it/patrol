@extends('layouts.portal')
@section('title', 'PBG')

@section('content')
@php
    $ikon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Permintaan Barang Gudang</h1>
        <p class="text-sm text-gray-600">{{ $semua ? 'Semua permintaan yang masuk.' : 'Permintaan Anda dan yang menunggu persetujuan Anda.' }}</p>
    </div>
    <a href="{{ route('portal.pbg.create') }}" class="pt-btn pt-btn-utama">Buat PBG</a>
</div>

<div class="pt-card overflow-hidden">
    @forelse($daftar as $p)
        <a href="{{ route('portal.pbg.show', $p) }}" class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
            <span class="pt-baris-ikon pt-i-nila mt-0.5">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-semibold text-gray-900">{{ $p->nomor }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $p->warnaStatus() }}">{{ $p->labelStatus() }}</span>
                </div>
                <div class="text-sm text-gray-700 mt-1">
                    {{ $p->jumlahJenis() }} jenis barang &middot; {{ Str::limit(collect($p->barisBarang())->pluck('nama')->join(', '), 70) }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $p->departemen }}{{ $p->unit_kerja ? ' / ' . $p->unit_kerja : '' }}
                    @if($semua) &middot; {{ $p->nama }} @endif
                    &middot; {{ $p->tgl_pbg->format('d M Y') }}
                </div>
            </div>
        </a>
    @empty
        <div class="pt-kosong">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
            <p class="text-sm text-gray-600">Belum ada permintaan barang.</p>
            <a href="{{ route('portal.pbg.create') }}" class="pt-btn pt-btn-utama pt-btn-kecil mt-3">Buat PBG</a>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $daftar->links() }}</div>
@endsection
