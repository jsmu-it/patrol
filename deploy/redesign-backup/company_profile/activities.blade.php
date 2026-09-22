@extends('layouts.company_profile')

@section('title', 'Kegiatan — PT. Jaya Sakti Mandiri Unggul')

@section('content')
@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-kegiatan.jpg'))
        ? asset('assets/dummy/pagehead-kegiatan.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Kegiatan</p>
        <h1 class="jsm-h1">Kegiatan</h1>
        <p>Dokumentasi apel, pelatihan, kunjungan lokasi, dan kegiatan internal perusahaan.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container">
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:32px">
            <a href="{{ route('activities') }}" class="jsm-chip {{ request('type') ? '' : 'jsm-chip-type' }}">Semua kegiatan</a>
            <a href="{{ route('activities', ['type' => 'internal']) }}" class="jsm-chip {{ request('type') === 'internal' ? 'jsm-chip-type' : '' }}">Kegiatan internal</a>
        </div>

        @if($activities->isNotEmpty())
            <div class="jsm-grid3">
                @foreach($activities as $activity)
                <article class="jsm-card" style="display:flex;flex-direction:column">
                    <div style="border-bottom:1px solid var(--line);background:var(--sand)">
                        @if($activity->image)
                            <img src="{{ asset('storage/' . $activity->image) }}" alt="{{ $activity->title }}" loading="lazy" width="600" height="360" style="display:block;width:100%;height:190px;object-fit:cover">
                        @else
                            <div class="jsm-imgbox-empty" style="min-height:190px">Tanpa dokumentasi foto</div>
                        @endif
                    </div>
                    <div class="jsm-card-pad" style="flex:1;display:flex;flex-direction:column">
                        <div style="font-size:14px;color:var(--faint);margin-bottom:10px">
                            {{ $activity->date ? $activity->date->format('d M Y') : '—' }}@if($activity->type) &middot; {{ ucfirst($activity->type) }}@endif
                        </div>
                        <h2 class="jsm-h3" style="margin-bottom:8px"><a href="{{ route('activities.show', $activity) }}" style="color:inherit;text-decoration:none">{{ $activity->title }}</a></h2>
                        <p class="jsm-muted" style="font-size:14.5px;flex:1">{{ Str::limit(strip_tags($activity->short_description ?? ''), 120) }}</p>
                        <a href="{{ route('activities.show', $activity) }}" class="jsm-link" style="margin-top:14px;align-self:flex-start">Baca selengkapnya</a>
                    </div>
                </article>
                @endforeach
            </div>
        @else
            <div class="jsm-empty">
                <p class="jsm-empty-t">Belum ada kegiatan yang dipublikasikan</p>
                <p class="jsm-empty-d">Dokumentasi apel pagi, pelatihan, dan kunjungan lokasi akan ditampilkan di halaman ini.</p>
            </div>
        @endif
    </div>
</section>
@endsection
