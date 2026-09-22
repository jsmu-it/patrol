@extends('layouts.company_profile')

@section('title', 'Pertanyaan Umum — PT. Jaya Sakti Mandiri Unggul')

@section('content')
@php
    $fallback = [
        ['Apakah perusahaan ini berizin sebagai BUJP?', 'Ya. Salinan izin operasional dan dokumen legalitas dapat kami kirimkan untuk keperluan verifikasi tender.'],
        ['Berapa lama proses penempatan anggota?', 'Bergantung jumlah titik dan hasil kajian lokasi. Untuk kebutuhan standar, penempatan umumnya dapat dimulai setelah kesepakatan lingkup kerja dan kajian kerawanan selesai.'],
        ['Bagaimana kehadiran anggota dipantau?', 'Melalui sistem absensi dan patroli digital milik kami sendiri. Kehadiran diverifikasi dengan lokasi, dan patroli tercatat per titik sehingga dapat diperiksa klien.'],
    ];
@endphp

@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-kontak.jpg'))
        ? asset('assets/dummy/pagehead-kontak.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; FAQ</p>
        <h1 class="jsm-h1">Pertanyaan Umum</h1>
        <p>Hal yang paling sering ditanyakan sebelum kerja sama dimulai.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container" style="max-width:820px">
        @if($faqs->isNotEmpty())
            <div x-data="{ cat:'all', open:null }">
                @if($categories->isNotEmpty())
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:28px" role="group" aria-label="Saring menurut kategori">
                    <button type="button" class="jsm-chip" :class="cat==='all' ? 'jsm-chip-type' : ''" @click="cat='all'" style="cursor:pointer">Semua</button>
                    @foreach($categories as $category)
                    <button type="button" class="jsm-chip" :class="cat==='{{ Str::slug($category) }}' ? 'jsm-chip-type' : ''" @click="cat='{{ Str::slug($category) }}'" style="cursor:pointer">{{ $category }}</button>
                    @endforeach
                </div>
                @endif

                <div class="jsm-acc">
                    @foreach($faqs as $faq)
                    <div class="jsm-acc-item" x-show="cat==='all' || cat==='{{ Str::slug($faq->category) }}'">
                        <h2 style="margin:0">
                            <button type="button" class="jsm-acc-btn" @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}" :aria-expanded="open === {{ $faq->id }} ? 'true' : 'false'" aria-controls="faq-{{ $faq->id }}">
                                <span>{{ $faq->question }}</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </h2>
                        <div class="jsm-acc-panel" id="faq-{{ $faq->id }}" x-show="open === {{ $faq->id }}" x-cloak>{!! nl2br(e($faq->answer)) !!}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="jsm-acc" x-data="{ open:null }">
                @foreach($fallback as $i => $f)
                <div class="jsm-acc-item">
                    <h2 style="margin:0">
                        <button type="button" class="jsm-acc-btn" @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }} ? 'true' : 'false'" aria-controls="fb-{{ $i }}">
                            <span>{{ $f[0] }}</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </h2>
                    <div class="jsm-acc-panel" id="fb-{{ $i }}" x-show="open === {{ $i }}" x-cloak>{{ $f[1] }}</div>
                </div>
                @endforeach
            </div>
        @endif

        <div class="jsm-card jsm-card-pad" style="margin-top:40px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between">
            <div>
                <p class="jsm-h3" style="margin:0 0 4px">Pertanyaan Anda belum terjawab?</p>
                <p class="jsm-muted" style="font-size:14.5px;margin:0">Sampaikan langsung, kami balas pada hari kerja.</p>
            </div>
            <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-primary">Hubungi Kami</a>
        </div>
    </div>
</section>
@endsection
