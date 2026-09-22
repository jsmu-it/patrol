@extends('layouts.portal')
@section('title', 'Meeting')

@section('content')
@php
    $ikon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>';
@endphp

<div class="max-w-4xl mx-auto" x-data="{ buat: false }">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Meeting</h1>
            <p class="text-sm text-gray-600">Rapat daring lewat peramban — video, suara, berbagi layar, dan obrolan.</p>
        </div>
        <button type="button" class="pt-btn pt-btn-utama" @click="buat = !buat">Rapat Baru</button>
    </div>

    {{-- Membuat rapat --}}
    <div class="pt-card pt-card-pad mb-4" x-show="buat" x-cloak>
        <div class="pt-judul mb-3">Rapat Baru</div>
        <form action="{{ route('portal.meeting.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul rapat <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required value="{{ old('title') }}" placeholder="mis. Koordinasi Mingguan Operasional" class="pt-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="opsional" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jadwal</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="pt-input">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan bila mau langsung mulai sekarang.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kata sandi ruangan</label>
                    <input type="text" name="password" value="{{ old('password') }}" placeholder="opsional, minimal 4 karakter" class="pt-input">
                </div>
            </div>
            <button type="submit" class="pt-btn pt-btn-utama">Buat &amp; Mulai</button>
        </form>
    </div>

    {{-- Rapat yang sedang atau akan berlangsung --}}
    <div class="pt-card overflow-hidden mb-4">
        @forelse($meetings as $m)
            <div class="flex flex-wrap items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0">
                <span class="pt-baris-ikon {{ $m->status === 'active' ? 'pt-i-hijau' : 'pt-i-ungu' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-semibold text-gray-900">{{ $m->title }}</span>
                        @if($m->status === 'active')
                            <span class="text-xs px-2 py-0.5 rounded-full pt-i-hijau">Sedang berlangsung</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-full pt-i-kuning">Terjadwal</span>
                        @endif
                        @if($m->password)
                            <span class="text-xs text-gray-400" title="Ruangan berkata sandi">&#128274;</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Tuan rumah {{ $m->host?->name ?? '-' }}
                        @if($m->scheduled_at) &middot; {{ $m->scheduled_at->format('d M Y H:i') }} @endif
                        @if($m->description) &middot; {{ Str::limit($m->description, 60) }} @endif
                    </div>
                </div>
                <a href="{{ route('portal.meeting.room', $m) }}" class="pt-btn pt-btn-utama pt-btn-kecil">Gabung</a>
            </div>
        @empty
            <div class="pt-kosong">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ikon !!}</svg>
                <p class="text-sm text-gray-600">Belum ada rapat yang berjalan.</p>
                <button type="button" class="pt-btn pt-btn-utama pt-btn-kecil mt-3" @click="buat = true">Buat rapat</button>
            </div>
        @endforelse
    </div>

    @if($riwayat->isNotEmpty())
        <div class="pt-card pt-card-pad">
            <div class="pt-judul mb-3">Rapat Anda yang sudah selesai</div>
            @foreach($riwayat as $m)
                <div class="flex items-center justify-between gap-3 py-2 text-sm border-b border-gray-100 last:border-0">
                    <span class="text-gray-700">{{ $m->title }}</span>
                    <span class="text-xs text-gray-400">{{ $m->ended_at?->format('d M Y H:i') }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
