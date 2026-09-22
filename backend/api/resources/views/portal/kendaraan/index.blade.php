@extends('layouts.portal')
@section('title', 'Permintaan Kendaraan')

@section('content')
@php
    $ikon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 17H3v-5l2-5h9l3 5h3a1 1 0 011 1v4h-1M8 17h8"/>';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Permintaan Kendaraan</h1>
        <p class="text-sm text-gray-600">{{ $semua ? 'Semua permintaan yang masuk.' : 'Permintaan Anda dan yang menunggu persetujuan Anda.' }}</p>
    </div>
    <a href="{{ route('portal.kendaraan.create') }}" class="pt-btn pt-btn-utama">Buat Permintaan</a>
</div>

<div class="pt-card overflow-hidden">
    @forelse($daftar as $p)
        <a href="{{ route('portal.kendaraan.show', $p) }}" class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
            <span class="pt-baris-ikon pt-i-kuning mt-0.5">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-semibold text-gray-900">{{ $p->nomor }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $p->warnaStatus() }}">{{ $p->labelStatus() }}</span>
                </div>
                <div class="text-sm text-gray-700 mt-1">{{ Str::limit($p->keperluan, 80) }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $p->labelHariTanggal() }} &middot; {{ $p->labelJam() }}
                    @if($semua) &middot; {{ $p->nama }} ({{ $p->bagian }}) @endif
                    @if($p->driver) &middot; {{ $p->driver }} / {{ $p->no_polisi }} @endif
                </div>
            </div>
        </a>
    @empty
        <div class="pt-kosong">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
            <p class="text-sm text-gray-600">Belum ada permintaan kendaraan.</p>
            <a href="{{ route('portal.kendaraan.create') }}" class="pt-btn pt-btn-utama pt-btn-kecil mt-3">Buat permintaan</a>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $daftar->links() }}</div>
@endsection
