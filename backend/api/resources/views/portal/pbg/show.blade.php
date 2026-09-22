@extends('layouts.portal')
@section('title', 'PBG ' . $pbg->nomor)

@section('content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('portal.pbg.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar PBG</a>

    <div class="flex flex-wrap items-center justify-between gap-3 mt-2 mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-xl font-bold text-gray-900">{{ $pbg->nomor }}</h1>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $pbg->warnaStatus() }}">{{ $pbg->labelStatus() }}</span>
        </div>
        <a href="{{ route('portal.pbg.pdf', $pbg) }}" class="pt-btn pt-btn-garis pt-btn-kecil">Unduh PDF</a>
    </div>

    {{-- Keputusan kepala bagian --}}
    @if($bolehSetujui)
        <div class="pt-card pt-card-pad mb-4" style="border-color:#BFDBFE;background:#EFF6FF" x-data="{ tolak: false }">
            <div class="pt-judul mb-1">Permintaan ini menunggu persetujuan Anda</div>
            <p class="text-xs text-gray-600 mb-3">Tahap: Menyetujui (Kepala Bagian)</p>
            <form action="{{ route('portal.pbg.putuskan', $pbg) }}" method="POST" class="space-y-3">
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

    {{-- Proses gudang --}}
    @if($bolehProses || $pbg->status === 'selesai')
        <div class="pt-card pt-card-pad mb-4" style="border-color:#FDE68A;background:#FFFBEB">
            <div class="pt-judul mb-1">Gudang</div>
            @if($bolehProses)
                <p class="text-xs text-gray-600 mb-3">Tandai bila barangnya sudah diserahkan.</p>
                <form action="{{ route('portal.pbg.proses', $pbg) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mengetahui (Kep. Gudang)</label>
                            <input type="text" name="kepala_gudang" value="{{ old('kepala_gudang', $pbg->kepala_gudang) }}"
                                   placeholder="nama kepala gudang" class="pt-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <input type="text" name="catatan_gudang" value="{{ old('catatan_gudang', $pbg->catatan_gudang) }}"
                                   placeholder="mis. stok kurang, diserahkan 3 dari 5" class="pt-input">
                        </div>
                    </div>
                    <button type="submit" class="pt-btn pt-btn-utama">Barang Diserahkan</button>
                </form>
            @else
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-sm">
                    <div><dt class="text-gray-500 text-xs">Diserahkan oleh</dt><dd class="text-gray-900 font-medium">{{ $pbg->petugasGudang->name ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Tanggal</dt><dd class="text-gray-900">{{ $pbg->gudang_pada?->format('d M Y H:i') ?: '-' }}</dd></div>
                    @if($pbg->kepala_gudang)
                        <div><dt class="text-gray-500 text-xs">Mengetahui</dt><dd class="text-gray-900">{{ $pbg->kepala_gudang }}</dd></div>
                    @endif
                    @if($pbg->catatan_gudang)
                        <div><dt class="text-gray-500 text-xs">Catatan</dt><dd class="text-gray-900">{{ $pbg->catatan_gudang }}</dd></div>
                    @endif
                </dl>
            @endif
        </div>
    @endif

    @if($pbg->status === 'ditolak' && $pbg->alasan_penolakan)
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            Ditolak: {{ $pbg->alasan_penolakan }}
        </div>
    @endif

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">Keterangan Permintaan</div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
            <div><dt class="text-gray-500 text-xs">Nama pemohon</dt><dd class="text-gray-900">{{ $pbg->nama }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Tgl. PBG</dt><dd class="text-gray-900">{{ $pbg->tgl_pbg->format('d M Y') }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Departemen</dt><dd class="text-gray-900">{{ $pbg->departemen }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Unit kerja</dt><dd class="text-gray-900">{{ $pbg->unit_kerja ?: '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Tgl. penggunaan</dt><dd class="text-gray-900">{{ $pbg->tgl_penggunaan?->format('d M Y') ?: '—' }}</dd></div>
        </dl>
    </div>

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">Barang yang Diminta</div>
        <div style="overflow-x:auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b border-gray-200">
                        <th class="py-2 pr-3">No</th>
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Nama barang dan spesifikasi</th>
                        <th class="py-2 pr-3">Jumlah</th>
                        <th class="py-2 pr-3">Satuan</th>
                        <th class="py-2">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pbg->barisBarang() as $i => $b)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-2 pr-3 text-gray-500">{{ $i + 1 }}</td>
                            <td class="py-2 pr-3 text-gray-700">{{ $b['kode'] ?: '-' }}</td>
                            <td class="py-2 pr-3 text-gray-900">{{ $b['nama'] }}</td>
                            <td class="py-2 pr-3 text-gray-700">{{ $b['jumlah'] ?: '-' }}</td>
                            <td class="py-2 pr-3 text-gray-700">{{ $b['satuan'] ?: '-' }}</td>
                            <td class="py-2 text-gray-700">{{ $b['keterangan'] ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="pt-card pt-card-pad">
        <div class="pt-judul mb-3">Tanda Tangan</div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="text-xs text-gray-500">Diajukan oleh</div>
                <div class="text-gray-900 font-medium">{{ $pbg->nama }}</div>
                <div class="text-xs text-gray-500">Pemohon &middot; {{ $pbg->created_at->format('d M Y') }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Menyetujui</div>
                <div class="text-gray-900 font-medium">{{ $pbg->kepalaBagian->name ?? 'Menunggu' }}</div>
                <div class="text-xs text-gray-500">Kepala Bagian &middot; {{ $pbg->kabag_pada?->format('d M Y') ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Mengetahui</div>
                <div class="text-gray-900 font-medium">{{ $pbg->kepala_gudang ?: '—' }}</div>
                <div class="text-xs text-gray-500">Kep. Gudang</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Diterima</div>
                <div class="text-gray-900 font-medium">{{ $pbg->petugasGudang->name ?? 'Menunggu' }}</div>
                <div class="text-xs text-gray-500">Petugas Gudang &middot; {{ $pbg->gudang_pada?->format('d M Y') ?: '—' }}</div>
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
