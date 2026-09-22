@extends('layouts.portal')
@section('title', 'Kartu JOC')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">JSMU Observation Card</h1>
        <p class="text-sm text-gray-600">{{ $hse ? 'Semua kartu pengamatan yang masuk.' : 'Kartu pengamatan yang Anda kirim.' }}</p>
    </div>
    <a href="{{ route('portal.joc.create') }}" class="pt-btn pt-btn-utama">Buat Kartu</a>
</div>

<div class="pt-card overflow-hidden">
    @forelse($kartu as $k)
        <a href="{{ route('portal.joc.show', $k) }}" class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
            <span class="pt-baris-ikon {{ $k->jenis_temuan === 'kondisi_tidak_aman' ? 'pt-i-kuning' : 'pt-i-merah' }} mt-0.5">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0l-7.1 12.25A2 2 0 004.99 19z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-semibold text-gray-900">{{ $k->nomor }}</span>
                    <span class="pt-lencana">{{ $k->labelJenis() }}</span>
                    @php $w = ['baru'=>'pt-i-merah','diproses'=>'pt-i-kuning','selesai'=>'pt-i-hijau'][$k->status]; @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $w }}">{{ \App\Models\JocCard::daftarStatus()[$k->status] }}</span>
                </div>
                <div class="text-sm text-gray-700 mt-1 truncate">{{ Str::limit($k->rincian_temuan, 90) }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $k->lokasi }} &middot; {{ $k->tanggal->format('d M Y') }} {{ substr($k->jam, 0, 5) }}
                    @if($hse) &middot; oleh {{ $k->user->name ?? $k->nama }} @endif
                </div>
            </div>
        </a>
    @empty
        <div class="pt-kosong">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm text-gray-600">Belum ada kartu pengamatan.</p>
            <a href="{{ route('portal.joc.create') }}" class="pt-btn pt-btn-utama pt-btn-kecil mt-3">Buat kartu pertama</a>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $kartu->links() }}</div>
@endsection
