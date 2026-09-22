@extends('layouts.portal')
@section('title', 'RPTK')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Rencana Permintaan Tenaga Kerja</h1>
        <p class="text-sm text-gray-600">{{ $semua ? 'Semua permintaan yang masuk.' : 'Permintaan yang Anda ajukan.' }}</p>
    </div>
    <a href="{{ route('portal.rptk.create') }}" class="pt-btn pt-btn-utama">Buat Permintaan</a>
</div>

<div class="pt-card overflow-hidden">
    @forelse($daftar as $r)
        <a href="{{ route('portal.rptk.show', $r) }}" class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
            <span class="pt-baris-ikon pt-i-hijau mt-0.5">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.36-1.86M17 20H7m10 0v-2c0-.66-.13-1.3-.36-1.86M7 20H2v-2a3 3 0 015.36-1.86M7 20v-2c0-.66.13-1.3.36-1.86m0 0a5 5 0 019.28 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-semibold text-gray-900">{{ $r->nomor }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $r->warnaStatus() }}">{{ $r->labelStatus() }}</span>
                </div>
                <div class="text-sm text-gray-700 mt-1">{{ $r->jabatan }} &middot; {{ $r->jumlah }} orang</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $r->divisi }} &middot; {{ $r->lokasi_kerja }}
                    @if($semua) &middot; oleh {{ $r->nama }} @endif
                    &middot; {{ $r->created_at->diffForHumans() }}
                </div>
            </div>
        </a>
    @empty
        <div class="pt-kosong">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.36-1.86M17 20H7m10 0v-2c0-.66-.13-1.3-.36-1.86M7 20H2v-2a3 3 0 015.36-1.86M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-sm text-gray-600">Belum ada permintaan tenaga kerja.</p>
            <a href="{{ route('portal.rptk.create') }}" class="pt-btn pt-btn-utama pt-btn-kecil mt-3">Buat permintaan</a>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $daftar->links() }}</div>
@endsection
