@extends('layouts.company_profile')

@section('title', $activity->title . ' — PT. Jaya Sakti Mandiri Unggul')

@section('content')
@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-kegiatan.jpg'))
        ? asset('assets/dummy/pagehead-kegiatan.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; <a href="{{ route('activities') }}">Kegiatan</a></p>
        <p class="jsm-mono" style="color:var(--faint)" >
            {{ $activity->date ? $activity->date->format('d F Y') : '' }}@if($activity->type) &middot; {{ ucfirst($activity->type) }}@endif
        </p>
        <h1 class="jsm-h1" style="margin-top:6px">{{ $activity->title }}</h1>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container" style="max-width:820px">
        @if($activity->image)
            <figure style="margin:0 0 32px">
                <div class="jsm-imgbox"><img src="{{ asset('storage/' . $activity->image) }}" alt="{{ $activity->title }}" width="1000" height="600" style="display:block;width:100%;height:auto"></div>
            </figure>
        @endif
        <div class="jsm-body">{!! $activity->content ?: $activity->short_description !!}</div>
        <p style="margin-top:40px"><a href="{{ route('activities') }}" class="jsm-link">&larr; Kembali ke daftar kegiatan</a></p>
    </div>
</section>
@endsection
