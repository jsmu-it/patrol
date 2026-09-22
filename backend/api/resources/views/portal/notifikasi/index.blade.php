@extends('layouts.portal')
@section('title', 'Pemberitahuan')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Pemberitahuan</h1>
            <p class="text-sm text-gray-600">{{ auth()->user()->unreadNotifications->count() }} belum dibaca</p>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('portal.notifikasi.baca-semua') }}" method="POST">
                @csrf
                <button type="submit" class="pt-btn pt-btn-garis pt-btn-kecil">Tandai semua dibaca</button>
            </form>
        @endif
    </div>

    <div class="pt-card overflow-hidden">
        @forelse($daftar as $n)
            <a href="{{ route('portal.notifikasi.baca', $n->id) }}"
               class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 {{ $n->read_at ? '' : 'bg-blue-50' }}">
                <span class="pt-baris-ikon pt-i-biru mt-0.5">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-gray-900">{{ $n->data['judul'] ?? 'Pemberitahuan' }}</div>
                    <div class="text-sm text-gray-700 mt-0.5">{{ $n->data['pesan'] ?? '' }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                </div>
                @unless($n->read_at)<span class="w-2 h-2 rounded-full bg-blue-600 mt-2 flex-shrink-0"></span>@endunless
            </a>
        @empty
            <div class="pt-kosong">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1"/></svg>
                <p class="text-sm text-gray-600">Belum ada pemberitahuan.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $daftar->links() }}</div>
</div>
@endsection
