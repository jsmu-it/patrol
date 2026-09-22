@extends('layouts.company_profile')

@section('title', 'Profil Perusahaan — PT. Jaya Sakti Mandiri Unggul')

@section('content')
@php
    $company  = \App\Models\Setting::get('company_name', 'PT. Jaya Sakti Mandiri Unggul');
    $sections = [
        ['about','Tentang Kami'],
        ['visi-misi','Visi &amp; Misi'],
        ['hsse','HSSE'],
        ['archipelago','Cakupan Nusantara'],
    ];
@endphp

@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-profil.jpg'))
        ? asset('assets/dummy/pagehead-profil.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Profil</p>
        <h1 class="jsm-h1">Profil Perusahaan</h1>
        <p>{{ $company }}</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container">
        <div class="jsm-withtoc">
            <nav class="hidden lg:block" aria-label="Daftar isi halaman">
                <div class="jsm-toc">
                    <span class="jsm-label" style="display:block;margin-bottom:14px">Daftar isi</span>
                    @foreach($sections as $s)
                        <a href="#{{ $s[0] }}">{!! $s[1] !!}</a>
                    @endforeach
                </div>
            </nav>

            <div>
                <article id="about" style="scroll-margin-top:96px;margin-bottom:56px">
                    <span class="jsm-label">01 — Tentang Kami</span>
                    <h2 class="jsm-h1" style="font-size:28px;margin:10px 0 18px">Tentang Kami</h2>
                    <div class="jsm-body">
                        @if($about && $about->body)
                            {!! $about->body !!}
                        @else
                            <p class="jsm-muted">{{ $company }} menyediakan tenaga pengamanan bersertifikat beserta sistem pendukungnya. Uraian lengkap mengenai sejarah dan struktur perusahaan akan dilengkapi melalui panel admin.</p>
                        @endif
                    </div>
                    @if($about && $about->image)
                        <div class="jsm-imgbox" style="margin-top:24px"><img src="{{ asset('storage/' . $about->image) }}" alt="Tentang {{ $company }}" loading="lazy" width="900" height="500" style="display:block;width:100%;height:auto"></div>
                    @endif
                </article>

                <article id="visi-misi" style="scroll-margin-top:96px;margin-bottom:56px">
                    <span class="jsm-label">02 — Visi &amp; Misi</span>
                    <h2 class="jsm-h1" style="font-size:28px;margin:10px 0 18px">Visi &amp; Misi</h2>
                    <div class="jsm-grid2">
                        <div class="jsm-card jsm-card-pad" style="">
                            <span class="jsm-label">Visi</span>
                            <div class="jsm-body jsm-muted" style="margin-top:12px;font-size:15px">
                                {!! ($visi && $visi->body) ? $visi->body : '<p>Rumusan visi perusahaan belum diisi.</p>' !!}
                            </div>
                        </div>
                        <div class="jsm-card jsm-card-pad" style="">
                            <span class="jsm-label">Misi</span>
                            <div class="jsm-body jsm-muted" style="margin-top:12px;font-size:15px">
                                {!! ($misi && $misi->body) ? $misi->body : '<p>Rumusan misi perusahaan belum diisi.</p>' !!}
                            </div>
                        </div>
                    </div>
                </article>

                <article id="hsse" style="scroll-margin-top:96px;margin-bottom:56px">
                    <span class="jsm-label">03 — HSSE</span>
                    <h2 class="jsm-h1" style="font-size:28px;margin:10px 0 8px">Kesehatan, Keselamatan, Keamanan &amp; Lingkungan</h2>
                    <p class="jsm-muted" style="margin-bottom:20px">Klien industri dan energi mensyaratkan kepatuhan HSSE sebelum penempatan disetujui.</p>
                    @if($hsse && $hsse->body)
                        <div class="jsm-body">{!! $hsse->body !!}</div>
                    @else
                        <div class="jsm-tablewrap">
                            <table class="jsm-table">
                                <thead><tr><th>Aspek</th><th>Penerapan di lapangan</th></tr></thead>
                                <tbody>
                                    <tr><td>Kesehatan</td><td>Pemeriksaan kesehatan berkala bagi anggota sebelum dan selama penempatan</td></tr>
                                    <tr><td>Keselamatan</td><td>Alat pelindung diri sesuai risiko lokasi dan pengarahan keselamatan sebelum bertugas</td></tr>
                                    <tr><td>Keamanan</td><td>Prosedur tetap penjagaan, patroli, dan pelaporan kejadian</td></tr>
                                    <tr><td>Lingkungan</td><td>Kepatuhan pada aturan lingkungan yang berlaku di area klien</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="jsm-muted" style="font-size:13.5px;margin-top:12px">Tabel di atas adalah kerangka bawaan. Kebijakan HSSE resmi dapat diisi melalui panel admin.</p>
                    @endif
                    @if($hsse && $hsse->image)
                        <div class="jsm-imgbox" style="margin-top:24px"><img src="{{ asset('storage/' . $hsse->image) }}" alt="Kebijakan HSSE" loading="lazy" width="900" height="500" style="display:block;width:100%;height:auto"></div>
                    @endif
                </article>

                <article id="archipelago" style="scroll-margin-top:96px">
                    <span class="jsm-label">04 — Cakupan Nusantara</span>
                    <h2 class="jsm-h1" style="font-size:28px;margin:10px 0 18px">Cakupan Nusantara</h2>
                    <div class="jsm-body">
                        @if($archipelago && $archipelago->body)
                            {!! $archipelago->body !!}
                        @else
                            <p class="jsm-muted">Wilayah operasi perusahaan akan dirinci di bagian ini melalui panel admin.</p>
                        @endif
                    </div>
                    @if($archipelago && $archipelago->image)
                        <div class="jsm-imgbox" style="margin-top:24px"><img src="{{ asset('storage/' . $archipelago->image) }}" alt="Peta cakupan wilayah operasi" loading="lazy" width="1000" height="560" style="display:block;width:100%;height:auto"></div>
                    @else
                        <div class="jsm-imgbox jsm-imgbox-empty" style="margin-top:24px;min-height:220px">Peta cakupan wilayah belum diunggah</div>
                    @endif
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
