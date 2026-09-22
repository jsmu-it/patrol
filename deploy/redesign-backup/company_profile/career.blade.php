@extends('layouts.company_profile')

@section('title', 'Karier — PT. Jaya Sakti Mandiri Unggul')
@section('description', 'Lowongan anggota Satpam dan posisi pendukung di PT. Jaya Sakti Mandiri Unggul.')

@section('content')
@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-karier.jpg'))
        ? asset('assets/dummy/pagehead-karier.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Karier</p>
        <h1 class="jsm-h1">Karier</h1>
        <p>Lowongan yang sedang dibuka. Seluruh proses lamaran tanpa biaya apa pun.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container">
        @if($careers->isNotEmpty())
            {{-- Desktop: tabel. Ponsel: kartu bertumpuk. --}}
            <div class="hidden md:block jsm-tablewrap">
                <table class="jsm-table">
                    <thead><tr><th>Posisi</th><th>Penempatan</th><th>Jenis</th><th>Diperbarui</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                        @foreach($careers as $career)
                        <tr>
                            <td><span style="font-weight:600">{{ $career->title }}</span></td>
                            <td class="jsm-muted">{{ $career->location ?: '—' }}</td>
                            <td>@if($career->type)<span class="jsm-chip jsm-chip-type">{{ $career->type }}</span>@else — @endif</td>
                            <td class="jsm-mono jsm-muted" style="font-size:13px">{{ optional($career->updated_at)->format('d M Y') }}</td>
                            <td style="text-align:right"><a href="{{ route('career.apply-form', $career->id) }}" class="jsm-btn jsm-btn-secondary" style="padding:8px 14px;min-height:0">Lamar</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="md:hidden" style="display:flex;flex-direction:column;gap:16px">
                @foreach($careers as $career)
                <article class="jsm-card jsm-card-pad">
                    <h2 class="jsm-h3" style="margin:0 0 10px">{{ $career->title }}</h2>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
                        @if($career->location)<span class="jsm-chip">{{ $career->location }}</span>@endif
                        @if($career->type)<span class="jsm-chip jsm-chip-type">{{ $career->type }}</span>@endif
                    </div>
                    <p class="jsm-muted" style="font-size:14.5px">{{ Str::limit(strip_tags($career->description), 140) }}</p>
                    <a href="{{ route('career.apply-form', $career->id) }}" class="jsm-btn jsm-btn-primary" style="margin-top:16px;width:100%">Lamar posisi ini</a>
                </article>
                @endforeach
            </div>

            <div style="margin-top:40px">
                @foreach($careers as $career)
                <details style="border-bottom:1px solid var(--line);padding:16px 0">
                    <summary style="cursor:pointer;font-weight:600;font-size:15px">Rincian &amp; persyaratan — {{ $career->title }}</summary>
                    <div class="jsm-body jsm-muted" style="margin-top:14px;font-size:14.5px">
                        {!! $career->description !!}
                        @if($career->requirements)
                            <p style="margin-top:14px"><strong>Persyaratan</strong></p>
                            {!! $career->requirements !!}
                        @endif
                    </div>
                </details>
                @endforeach
            </div>
        @else
            <div class="jsm-empty">
                <p class="jsm-empty-t">Belum ada lowongan terbuka</p>
                <p class="jsm-empty-d">Lamaran umum tetap kami terima dan disimpan untuk kebutuhan penempatan berikutnya.</p>
                <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-primary">Kirim lamaran umum</a>
            </div>
        @endif

        <div class="jsm-card jsm-card-pad" style="margin-top:40px">
            <p class="jsm-h3" style="margin:0 0 6px">Waspada penipuan rekrutmen</p>
            <p class="jsm-muted" style="font-size:14.5px;margin:0">Seluruh tahapan seleksi di perusahaan kami <strong>tidak dipungut biaya</strong>. Kami tidak pernah meminta pembayaran untuk seragam, pelatihan, atau penempatan.</p>
        </div>
    </div>
</section>
@endsection
