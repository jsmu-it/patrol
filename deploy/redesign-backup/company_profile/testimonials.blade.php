@extends('layouts.company_profile')

@section('title', 'Testimoni — PT. Jaya Sakti Mandiri Unggul')

@section('content')
@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-klien.jpg'))
        ? asset('assets/dummy/pagehead-klien.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Testimoni</p>
        <h1 class="jsm-h1">Testimoni</h1>
        <p>Penilaian dari penanggung jawab lokasi yang bekerja langsung dengan anggota kami.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container">
        @if($testimonials->isNotEmpty())
            <div class="jsm-grid3">
                @foreach($testimonials as $t)
                <blockquote class="jsm-quote">
                    @if($t->rating)
                        <div class="jsm-mono" style="font-size:12px;color:var(--gold-ink);margin-bottom:10px">PENILAIAN {{ $t->rating }}/5</div>
                    @endif
                    <p class="jsm-quote-t">&ldquo;{{ $t->content }}&rdquo;</p>
                    <footer class="jsm-quote-by">
                        @if($t->client_photo)
                            <img class="jsm-quote-av" src="{{ asset('storage/' . $t->client_photo) }}" alt="" loading="lazy" width="38" height="38">
                        @endif
                        <div>
                            <div class="jsm-quote-name">{{ $t->client_name }}</div>
                            @if($t->client_position || $t->client_company)
                                <div class="jsm-quote-role">{{ trim(($t->client_position ?? '') . (($t->client_position && $t->client_company) ? ' · ' : '') . ($t->client_company ?? '')) }}</div>
                            @endif
                        </div>
                    </footer>
                </blockquote>
                @endforeach
            </div>
        @else
            <div class="jsm-empty">
                <p class="jsm-empty-t">Belum ada testimoni yang dipublikasikan</p>
                <p class="jsm-empty-d">Testimoni hanya ditampilkan setelah disetujui oleh klien yang bersangkutan.</p>
            </div>
        @endif
    </div>
</section>
@endsection
