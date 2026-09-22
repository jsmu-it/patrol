@extends('layouts.portal')
@section('title', 'Permintaan ' . $permintaan->nomor)

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('portal.kendaraan.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar Permintaan</a>

    <div class="flex flex-wrap items-center justify-between gap-3 mt-2 mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-xl font-bold text-gray-900">{{ $permintaan->nomor }}</h1>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $permintaan->warnaStatus() }}">{{ $permintaan->labelStatus() }}</span>
        </div>
        <a href="{{ route('portal.kendaraan.pdf', $permintaan) }}" class="pt-btn pt-btn-garis pt-btn-kecil">Unduh PDF</a>
    </div>

    {{-- Keputusan atasan langsung --}}
    @if($bolehSetujui)
        <div class="pt-card pt-card-pad mb-4" style="border-color:#BFDBFE;background:#EFF6FF" x-data="{ tolak: false }">
            <div class="pt-judul mb-1">Permintaan ini menunggu persetujuan Anda</div>
            <p class="text-xs text-gray-600 mb-3">Tahap: Atasan Langsung</p>
            <form action="{{ route('portal.kendaraan.putuskan', $permintaan) }}" method="POST" class="space-y-3">
                @csrf
                <div x-show="tolak" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan penolakan</label>
                    <textarea name="alasan_penolakan" rows="2" class="pt-input"></textarea>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" name="keputusan" value="setuju" class="pt-btn pt-btn-utama">Setujui</button>
                    <button type="submit" name="keputusan" value="tolak" @click="if(!tolak){ tolak = true; $event.preventDefault(); }"
                            class="pt-btn pt-btn-garis" style="color:#B91C1C;border-color:#FCA5A5">
                        <span x-text="tolak ? 'Kirim Penolakan' : 'Tolak'"></span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Catatan GA: driver & nomor polisi --}}
    @if($bolehTindak || ($permintaan->status === 'siap' && $permintaan->driver))
        <div class="pt-card pt-card-pad mb-4" style="border-color:#FDE68A;background:#FFFBEB">
            <div class="pt-judul mb-1">Catatan GA</div>
            @if($bolehTindak)
                <p class="text-xs text-gray-600 mb-3">Tetapkan driver dan kendaraan yang dipakai.</p>
                <form action="{{ route('portal.kendaraan.tindak', $permintaan) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Driver <span class="text-red-500">*</span></label>
                            <input type="text" name="driver" required value="{{ old('driver', $permintaan->driver) }}" class="pt-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. polisi <span class="text-red-500">*</span></label>
                            <input type="text" name="no_polisi" required value="{{ old('no_polisi', $permintaan->no_polisi) }}" placeholder="mis. B 1234 XYZ" class="pt-input">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan tambahan</label>
                        <textarea name="catatan_ga" rows="2" class="pt-input">{{ old('catatan_ga', $permintaan->catatan_ga) }}</textarea>
                    </div>
                    <button type="submit" class="pt-btn pt-btn-utama">Simpan &amp; Kabari Pemohon</button>
                </form>
            @else
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-sm">
                    <div><dt class="text-gray-500 text-xs">Driver</dt><dd class="text-gray-900 font-medium">{{ $permintaan->driver }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">No. polisi</dt><dd class="text-gray-900 font-medium">{{ $permintaan->no_polisi }}</dd></div>
                    @if($permintaan->catatan_ga)
                        <div class="sm:col-span-2"><dt class="text-gray-500 text-xs">Catatan</dt><dd class="text-gray-900">{{ $permintaan->catatan_ga }}</dd></div>
                    @endif
                </dl>
            @endif
        </div>
    @endif

    @if($permintaan->status === 'ditolak' && $permintaan->alasan_penolakan)
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            Ditolak: {{ $permintaan->alasan_penolakan }}
        </div>
    @endif

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">Rincian Permintaan</div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
            <div><dt class="text-gray-500 text-xs">Nama</dt><dd class="text-gray-900">{{ $permintaan->nama }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Bagian</dt><dd class="text-gray-900">{{ $permintaan->bagian }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Hari / tanggal</dt><dd class="text-gray-900">{{ $permintaan->labelHariTanggal() }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Jam</dt><dd class="text-gray-900">{{ $permintaan->labelJam() }}</dd></div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500 text-xs">Keperluan</dt>
                <dd class="text-gray-900 whitespace-pre-line">{{ $permintaan->keperluan }}</dd>
            </div>
        </dl>
    </div>

    <div class="pt-card pt-card-pad">
        <div class="pt-judul mb-3">Tanda Tangan</div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <div class="text-xs text-gray-500">Pemohon</div>
                <div class="text-gray-900 font-medium">{{ $permintaan->nama }}</div>
                <div class="text-xs text-gray-500">{{ $permintaan->created_at->format('d M Y') }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Atasan langsung</div>
                <div class="text-gray-900 font-medium">{{ $permintaan->atasan->name ?? 'Menunggu' }}</div>
                <div class="text-xs text-gray-500">{{ $permintaan->atasan_pada?->format('d M Y') ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Bagian GA</div>
                <div class="text-gray-900 font-medium">{{ $permintaan->petugasGa->name ?? 'Menunggu' }}</div>
                <div class="text-xs text-gray-500">{{ $permintaan->ga_pada?->format('d M Y') ?: '—' }}</div>
            </div>
        </div>

        @if($tahap && $tahap->ada())
            <p class="text-xs text-gray-500 mt-4 pt-3 border-t border-gray-100">
                Sekarang menunggu {{ $tahap->peran }}: {{ $tahap->label() }}.
                @if($tahap->pengganti) <span class="text-yellow-700">{{ $tahap->catatan }}</span> @endif
            </p>
        @endif
    </div>
</div>
@endsection
