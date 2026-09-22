@extends('layouts.company_profile')

@section('title', 'Klien — PT. Jaya Sakti Mandiri Unggul')

@section('content')
@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-klien.jpg'))
        ? asset('assets/dummy/pagehead-klien.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Klien</p>
        <h1 class="jsm-h1">Klien</h1>
        <p>Perusahaan dan institusi yang menempatkan pengamanannya pada kami.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container">
        @if($clients->isNotEmpty())
            <div class="jsm-logos">
                @foreach($clients as $client)
                <div class="jsm-logo">
                    @if($client->logo)
                        @if($client->website)
                            <a href="{{ $client->website }}" target="_blank" rel="noopener" aria-label="{{ $client->name }}"><img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}" loading="lazy"></a>
                        @else
                            <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}" loading="lazy">
                        @endif
                    @else
                        <span class="jsm-logo-name">{{ $client->name }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div class="jsm-empty">
                <p class="jsm-empty-t">Daftar klien belum dipublikasikan</p>
                <p class="jsm-empty-d">Sebagian klien meminta namanya tidak ditampilkan secara terbuka. Daftar referensi dapat kami kirimkan atas permintaan.</p>
                <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-secondary">Minta daftar referensi</a>
            </div>
            <div style="margin-top:40px">
                <span class="jsm-label">Sektor yang dilayani</span>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:14px">
                    @foreach(['Kawasan industri','Perbankan','Fasilitas kesehatan','Perkantoran','Pergudangan &amp; logistik','Ritel','Pendidikan','Properti hunian'] as $s)
                        <span class="jsm-chip">{!! $s !!}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
