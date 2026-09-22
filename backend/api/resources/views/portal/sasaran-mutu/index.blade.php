@extends('layouts.portal')
@section('title', 'Sasaran Mutu')

@section('content')
@php
    $ikon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17V9m4 8V5m4 12v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Pencapaian Sasaran Mutu</h1>
        <p class="text-sm text-gray-600">{{ $semua ? 'Semua laporan yang masuk.' : 'Laporan Anda dan yang menunggu keputusan Anda.' }}</p>
    </div>
    <a href="{{ route('portal.sasaran-mutu.create') }}" class="pt-btn pt-btn-utama">Buat Laporan</a>
</div>

<div class="pt-card overflow-hidden">
    @forelse($daftar as $l)
        <a href="{{ route('portal.sasaran-mutu.show', $l) }}" class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
            <span class="pt-baris-ikon pt-i-ungu mt-0.5">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-semibold text-gray-900">{{ $l->nomor }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $l->warnaStatus() }}">{{ $l->labelStatus() }}</span>
                    <span class="pt-chip">{{ $l->labelBulan() }}</span>
                </div>
                <div class="text-sm text-gray-700 mt-1">{{ $l->labelDepartemen() }} &middot; {{ count($l->barisSasaran()) }} sasaran</div>
                <div class="text-xs text-gray-500 mt-1">
                    @if($semua) oleh {{ $l->nama }} &middot; @endif
                    {{ $l->created_at->diffForHumans() }}
                </div>
            </div>
        </a>
    @empty
        <div class="pt-kosong">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
            <p class="text-sm text-gray-600">Belum ada laporan sasaran mutu.</p>
            <a href="{{ route('portal.sasaran-mutu.create') }}" class="pt-btn pt-btn-utama pt-btn-kecil mt-3">Buat laporan</a>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $daftar->links() }}</div>
@endsection
