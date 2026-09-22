@extends('layouts.portal')
@section('title', 'Tempat Sampah')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Tempat Sampah</h1>
        <p class="text-sm text-gray-600 mt-1">Item di sini masih bisa dipulihkan.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('portal.storage.index') }}" class="pt-btn pt-btn-garis pt-btn-kecil">Kembali</a>
        @if($items->isNotEmpty())
            <form action="{{ route('portal.storage.kosongkan') }}" method="POST"
                  onsubmit="return confirm('Kosongkan tempat sampah? Seluruh isinya dihapus permanen.');">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm">Kosongkan</button>
            </form>
        @endif
    </div>
</div>

<div class="pt-card overflow-hidden">
    @forelse($items as $item)
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0">
            <div class="min-w-0 flex-1">
                <div class="text-sm font-medium text-gray-900 truncate">{{ $item->name }}</div>
                <div class="text-xs text-gray-500">
                    {{ $item->isFolder() ? 'Folder' : $item->ukuranTerbaca() }} &middot; dibuang {{ $item->trashed_at->diffForHumans() }}
                </div>
            </div>
            <form action="{{ route('portal.storage.pulihkan', $item) }}" method="POST">
                @csrf
                <button type="submit" class="pt-btn pt-btn-garis pt-btn-kecil">Pulihkan</button>
            </form>
            <form action="{{ route('portal.storage.permanen', $item) }}" method="POST"
                  onsubmit="return confirm('Hapus permanen {{ addslashes($item->name) }}?');">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-1.5 rounded-lg text-sm text-red-600 hover:bg-red-50">Hapus permanen</button>
            </form>
        </div>
    @empty
        <div class="px-4 py-16 text-center text-sm text-gray-600">Tempat sampah kosong.</div>
    @endforelse
</div>
@endsection
