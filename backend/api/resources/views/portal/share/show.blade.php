@extends('layouts.portal')
@section('title', 'Folder Dibagikan — ' . $akar->name)

@section('content')
@php
    // $token null = dibuka dari sidebar oleh karyawan yang login
    $urlFolder = fn ($id) => $token
        ? ($id === $akar->id ? route('portal.share.show', $token) : route('portal.share.sub', [$token, $id]))
        : ($id === $akar->id ? route('portal.storage.divisi', $akar) : route('portal.storage.divisi.sub', [$akar, $id]));
    $urlBerkas = fn ($id) => $token
        ? route('portal.share.unduh', [$token, $id])
        : route('portal.storage.divisi.unduh', [$akar, $id]);
@endphp
<div class="max-w-4xl mx-auto">
    <div class="flex items-start gap-3 mb-4">
        <span class="w-10 h-10 rounded-lg bg-yellow-50 text-yellow-700 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
        </span>
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-gray-900 truncate">{{ $akar->name }}</h1>
            <p class="text-sm text-gray-600">{{ $akar->adalahArsipDivisi() ? 'Arsip divisi ' . $akar->divisi_pemilik : 'Dibagikan oleh ' . ($akar->user->name ?? 'Karyawan JSMU') }}</p>
        </div>
    </div>

    @if(count($jejak) > 1)
        <nav class="text-sm text-gray-500 mb-3 flex flex-wrap items-center gap-1">
            @foreach($jejak as $i => $j)
                @if($i > 0)<span>/</span>@endif
                @if($j->id === $folder->id)
                    <span class="text-gray-900">{{ $j->name }}</span>
                @else
                    <a href="{{ $urlFolder($j->id) }}" class="hover:text-gray-900">{{ $j->name }}</a>
                @endif
            @endforeach
        </nav>
    @endif

    <div class="pt-card overflow-hidden">
        @forelse($items as $item)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
                <span class="w-9 h-9 rounded-lg {{ $item->isFolder() ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center flex-shrink-0">
                    @if($item->isFolder())
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @endif
                </span>
                <div class="min-w-0 flex-1">
                    @if($item->isFolder())
                        <a href="{{ $urlFolder($item->id) }}" class="text-sm font-medium text-gray-900 hover:text-blue-800 truncate block">{{ $item->name }}</a>
                        <div class="text-xs text-gray-500">Folder</div>
                    @else
                        <a href="{{ $urlBerkas($item->id) }}" class="text-sm font-medium text-gray-900 hover:text-blue-800 truncate block">{{ $item->name }}</a>
                        <div class="text-xs text-gray-500">{{ $item->ukuranTerbaca() }}</div>
                    @endif
                </div>
                @if(! $item->isFolder())
                    @if(! $token && \App\Http\Controllers\Portal\SuntingDokumenController::jenis($item))
                        {{-- Karyawan yang login bisa membukanya langsung; pengunjung
                             lewat tautan berbagi tetap hanya mengunduh. --}}
                        <a href="{{ route('portal.storage.sunting', $item) }}" class="text-xs text-blue-700 hover:underline flex-shrink-0">Buka</a>
                    @endif
                    <a href="{{ $urlBerkas($item->id) }}" class="text-xs text-gray-500 hover:underline flex-shrink-0">Unduh</a>
                @endif
            </div>
        @empty
            <div class="px-4 py-16 text-center text-sm text-gray-600">Folder ini kosong.</div>
        @endforelse
    </div>

    <p class="mt-3 text-xs text-gray-500">
        {{ $token ? 'Anda membuka folder ini lewat tautan berbagi.' : 'Folder divisi milik rekan kerja.' }} Isinya hanya bisa dilihat dan diunduh.
    </p>
</div>
@endsection
