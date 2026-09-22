@extends('layouts.company_profile')

@section('title', 'Legalitas &amp; Pencapaian — PT. Jaya Sakti Mandiri Unggul')
@section('description', 'Izin operasional, sertifikasi, dan penghargaan PT. Jaya Sakti Mandiri Unggul.')

@section('content')
@php $licenseNo = \App\Models\Setting::get('license_number'); @endphp

@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-legalitas.jpg'))
        ? asset('assets/dummy/pagehead-legalitas.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Legalitas</p>
        <h1 class="jsm-h1">Legalitas &amp; Pencapaian</h1>
        <p>Dokumen perizinan dan sertifikasi yang menjadi dasar kami beroperasi.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container">
        @if($licenseNo)
        <div class="jsm-card jsm-card-pad" style="margin-bottom:48px">
            <span class="jsm-label">Izin operasional</span>
            <p class="jsm-mono" style="font-size:20px;color:var(--ink);margin-top:10px">{{ $licenseNo }}</p>
        </div>
        @endif

        @if($achievements->isNotEmpty())
            <div class="jsm-sechead">
                <span class="jsm-label">Daftar dokumen</span>
                <h2 class="jsm-h2">Perizinan, sertifikasi, dan penghargaan</h2>
                <span class="jsm-sechead-note">{{ $achievements->count() }} dokumen</span>
            </div>
            <div style="border-top:1px solid var(--line)">
                @foreach($achievements as $a)
                <article class="jsm-cred">
                    <div class="jsm-cred-doc">
                        @if($a->image)
                            <img src="{{ asset('storage/' . $a->image) }}" alt="Dokumen {{ $a->title }}" loading="lazy" width="300" height="400">
                        @else
                            <span class="jsm-cred-doc-none">Pindaian<br>belum<br>diunggah</span>
                        @endif
                    </div>
                    <div>
                        @if($a->year)<span class="jsm-cred-year">{{ $a->year }}</span>@endif
                        <h3 class="jsm-h3" style="margin:10px 0 8px">{{ $a->title }}</h3>
                        <div class="jsm-body jsm-muted" style="font-size:14.5px">{!! $a->description !!}</div>
                    </div>
                </article>
                @endforeach
            </div>
        @else
            <div class="jsm-empty">
                <p class="jsm-empty-t">Dokumen perizinan sedang dilengkapi</p>
                <p class="jsm-empty-d">Salinan izin operasional, sertifikasi, dan dokumen legalitas lain dapat kami kirimkan langsung untuk keperluan verifikasi tender atau kerja sama.</p>
                <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-primary">Minta salinan legalitas</a>
            </div>
        @endif
    </div>
</section>
@endsection
