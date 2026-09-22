@extends('layouts.portal')
@section('title', 'Beranda Portal')

@section('content')
@php
    $persen  = $kuota > 0 ? min(100, $terpakai / $kuota * 100) : 0;
    $keliling = 2 * M_PI * 44;                    // r=44 pada cincin SVG
    $isi      = $keliling * (1 - $persen / 100);

    $jam = (int) now()->format('H');
    $sapa = match (true) {
        $jam < 11 => 'Selamat pagi',
        $jam < 15 => 'Selamat siang',
        $jam < 19 => 'Selamat sore',
        default   => 'Selamat malam',
    };

    $menu = [
        ['Penyimpanan Data', 'Berkas kerja', route('portal.storage.index'), true, 'pt-i-biru',
         '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>'],
        ['JOC', 'Kartu pengamatan', route('portal.joc.index'), true, 'pt-i-merah',
         '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0l-7.1 12.25A2 2 0 004.99 19z"/>'],
        ['RPTK', 'Permintaan tenaga kerja', route('portal.rptk.index'), true, 'pt-i-hijau',
         '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.36-1.86M17 20H7m10 0v-2c0-.66-.13-1.3-.36-1.86M7 20H2v-2a3 3 0 015.36-1.86M7 20v-2c0-.66.13-1.3.36-1.86m0 0a5 5 0 019.28 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
        ['Sasaran Mutu', 'Laporan pencapaian', route('portal.sasaran-mutu.index'), true, 'pt-i-ungu',
         '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17V9m4 8V5m4 12v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
        ['PBG', 'Permintaan barang gudang', route('portal.pbg.index'), true, 'pt-i-nila',
         '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
        ['Meeting', 'Rapat daring', route('portal.meeting.index'), true, 'pt-i-ungu',
         '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>'],
        ['Permintaan Kendaraan', 'Ajukan pemakaian', route('portal.kendaraan.index'), true, 'pt-i-kuning',
         '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 17H3v-5l2-5h9l3 5h3a1 1 0 011 1v4h-1M8 17h8"/>'],
    ];
@endphp

{{-- Sambutan --}}
<div class="pt-hero mb-6">
    <div class="relative z-10">
        <h1>{{ $sapa }}, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        <p>Semua keperluan kerja Anda ada di satu tempat.</p>
        <div class="pt-hero-meta">
            <span>{{ now()->translatedFormat('l, d F Y') }}</span>
            <span>{{ $jumlahBerkas }} berkas tersimpan</span>
            <span>{{ \App\Models\PortalItem::formatUkuran($terpakai) }} terpakai</span>
        </div>
    </div>
</div>

{{-- Peluncur menu --}}
<div class="pt-launcher mb-8">
    @foreach($menu as [$nama, $ket, $href, $siap, $warna, $ikon])
        @if($siap)
            <a href="{{ $href }}" class="pt-tile pt-fokus">
                <span class="pt-tile-ikon {{ $warna }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
                </span>
                <span class="pt-tile-nama">{{ $nama }}</span>
                <span class="pt-tile-ket">{{ $ket }}</span>
            </a>
        @else
            <div class="pt-tile is-segera" title="Belum tersedia">
                <span class="pt-tile-ikon {{ $warna }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
                </span>
                <span class="pt-tile-nama">{{ $nama }}</span>
                <span class="pt-lencana">Segera</span>
            </div>
        @endif
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Cincin kuota --}}
    <div class="pt-card pt-card-pad">
        <div class="pt-judul mb-4">Ruang penyimpanan</div>
        <div class="flex items-center gap-4">
            <div class="pt-ring">
                <svg width="104" height="104" viewBox="0 0 104 104">
                    <circle cx="52" cy="52" r="44" fill="none" stroke="#EDEFF3" stroke-width="10"/>
                    <circle cx="52" cy="52" r="44" fill="none" stroke="#2563EB" stroke-width="10"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $keliling }}" stroke-dashoffset="{{ $isi }}"/>
                </svg>
                <div class="pt-ring-teks">
                    <span class="pt-ring-persen">{{ round($persen) }}%</span>
                    <span class="pt-ring-ket">terpakai</span>
                </div>
            </div>
            <div class="text-sm">
                <div class="font-semibold text-gray-900">{{ \App\Models\PortalItem::formatUkuran($terpakai) }}</div>
                <div class="text-gray-500">dari {{ \App\Models\PortalItem::formatUkuran($kuota) }}</div>
                <a href="{{ route('portal.storage.index') }}" class="inline-block mt-3 text-xs text-blue-700 hover:underline">Kelola berkas &rarr;</a>
            </div>
        </div>
    </div>

    {{-- Berkas terbaru --}}
    <div class="pt-card pt-card-pad lg:col-span-2">
        <div class="flex items-center justify-between mb-2">
            <div class="pt-judul">Berkas terbaru</div>
            <a href="{{ route('portal.storage.index') }}" class="text-xs text-blue-700 hover:underline">Lihat semua</a>
        </div>
        @forelse($terbaru as $f)
            <div class="pt-baris">
                <span class="pt-baris-ikon pt-i-biru">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-900 truncate">{{ $f->name }}</div>
                    <div class="text-xs text-gray-500">{{ $f->ukuranTerbaca() }} &middot; {{ $f->updated_at->diffForHumans() }}</div>
                </div>
                <a href="{{ route('portal.storage.unduh', $f) }}" class="pt-btn pt-btn-garis pt-btn-kecil">Unduh</a>
            </div>
        @empty
            <div class="pt-kosong">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                <p class="text-sm text-gray-600">Belum ada berkas.</p>
                <a href="{{ route('portal.storage.index') }}" class="pt-btn pt-btn-utama pt-btn-kecil mt-3">Mulai unggah</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
