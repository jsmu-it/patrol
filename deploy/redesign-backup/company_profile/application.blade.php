@extends('layouts.company_profile')

@section('title', 'Unduh Aplikasi — PT. Jaya Sakti Mandiri Unggul')
@section('description', 'Berkas aplikasi absensi dan patroli untuk anggota dan admin proyek.')

@section('content')
@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-layanan.jpg'))
        ? asset('assets/dummy/pagehead-layanan.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Unduh Aplikasi</p>
        <h1 class="jsm-h1">Unduh Aplikasi</h1>
        <p>Aplikasi absensi dan patroli untuk anggota di lokasi penempatan serta admin proyek.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container">
        @if($applications->isNotEmpty())
            <div class="jsm-tablewrap">
                <table class="jsm-table">
                    <thead><tr><th>Aplikasi</th><th>Platform</th><th>Versi</th><th>Ukuran</th><th><span class="sr-only">Unduh</span></th></tr></thead>
                    <tbody>
                        @foreach($applications as $app)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px">
                                    @if($app->icon)
                                        <img src="{{ asset('storage/' . $app->icon) }}" alt="" width="40" height="40" style="width:40px;height:40px;border-radius:2px;object-fit:cover;border:1px solid var(--line)">
                                    @endif
                                    <div>
                                        <div style="font-weight:600">{{ $app->name }}</div>
                                        @if($app->description)<div class="jsm-muted" style="font-size:13px">{{ Str::limit(strip_tags($app->description), 90) }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td>@if($app->platform)<span class="jsm-chip jsm-chip-type">{{ ucfirst($app->platform) }}</span>@else — @endif</td>
                            <td class="jsm-mono" style="font-size:13px">{{ $app->version ?: '—' }}</td>
                            <td class="jsm-mono jsm-muted" style="font-size:13px">{{ $app->file_size ?: '—' }}</td>
                            <td style="text-align:right">
                                <a href="{{ route('application.download', $app->id) }}" class="jsm-btn jsm-btn-secondary" style="padding:8px 14px;min-height:0">Unduh</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="jsm-muted" style="font-size:13.5px;margin-top:14px">Berkas hanya untuk anggota dan admin yang telah terdaftar. Masuk ke dashboard melalui <a href="{{ route('admin.login') }}" class="jsm-link">halaman absensi &amp; patroli</a>.</p>
        @else
            <div class="jsm-empty">
                <p class="jsm-empty-t">Belum ada berkas aplikasi yang dipublikasikan</p>
                <p class="jsm-empty-d">Anggota dan admin proyek untuk sementara dapat mengakses sistem melalui peramban.</p>
                <a href="{{ route('admin.login') }}" class="jsm-btn jsm-btn-primary">Buka Absensi &amp; Patroli</a>
            </div>
        @endif
    </div>
</section>
@endsection
