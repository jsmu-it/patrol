@extends('layouts.portal')
@section('title', $laporan->nomor)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
        <div>
            <a href="{{ route('portal.sasaran-mutu.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar Sasaran Mutu</a>
            <div class="flex flex-wrap items-center gap-2 mt-1">
                <h1 class="text-xl font-bold text-gray-900">{{ $laporan->nomor }}</h1>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $laporan->warnaStatus() }}">{{ $laporan->labelStatus() }}</span>
            </div>
            <p class="text-sm text-gray-600">
                {{ $laporan->labelDepartemen() }} &middot; periode {{ $laporan->labelBulan() }} &middot; oleh {{ $laporan->nama }}
            </p>
        </div>
        <a href="{{ route('portal.sasaran-mutu.pdf', $laporan) }}" class="pt-btn pt-btn-garis">Unduh PDF</a>
    </div>

    @if($bolehSetujui)
        <div class="pt-card pt-card-pad mb-4" style="border-color:#BFDBFE;background:#EFF6FF" x-data="{ tolak: false }">
            <div class="pt-judul mb-1">Laporan ini menunggu keputusan Anda</div>
            <p class="text-xs text-gray-600 mb-3">
                Tahap: {{ $laporan->status === 'menunggu_manager' ? 'Memeriksa (Kepala Departemen)' : 'Menyetujui (Direktur)' }}
            </p>
            <form action="{{ route('portal.sasaran-mutu.putuskan', $laporan) }}" method="POST" class="space-y-3">
                @csrf
                <div x-show="tolak" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan penolakan</label>
                    <textarea name="alasan_penolakan" rows="2" class="pt-input"></textarea>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" name="keputusan" value="setuju" class="pt-btn pt-btn-utama">
                        {{ $laporan->status === 'menunggu_manager' ? 'Periksa &amp; Teruskan' : 'Setujui' }}
                    </button>
                    <button type="submit" name="keputusan" value="tolak" @click="if(!tolak){ tolak = true; $event.preventDefault(); }"
                            class="pt-btn pt-btn-garis" style="color:#B91C1C;border-color:#FCA5A5">
                        <span x-text="tolak ? 'Kirim Penolakan' : 'Tolak'"></span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if($laporan->status === 'ditolak' && $laporan->alasan_penolakan)
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            Ditolak: {{ $laporan->alasan_penolakan }}
        </div>
    @endif

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">Plan &amp; Hasil</div>
        <div style="overflow-x:auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b border-gray-200">
                        <th class="py-2 pr-3">Sasaran strategi</th>
                        <th class="py-2 pr-3">Ukuran strategi</th>
                        <th class="py-2 pr-3">Target</th>
                        <th class="py-2 pr-3">Hasil</th>
                        <th class="py-2">Pencapaian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($laporan->barisSasaran() as $b)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-2 pr-3 text-gray-900">{{ $b['sasaran'] }}</td>
                            <td class="py-2 pr-3 text-gray-700">{{ $b['ukuran'] ?: '-' }}</td>
                            <td class="py-2 pr-3 text-gray-700">{{ trim(($b['target_ukuran'] ?? '') . ' ' . ($b['target_unit'] ?? '')) ?: '-' }}</td>
                            <td class="py-2 pr-3 text-gray-700">{{ $b['realisasi'] ?: '-' }}</td>
                            <td class="py-2 font-semibold text-gray-900">{{ $b['pencapaian'] ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="pt-card pt-card-pad">
            <div class="pt-judul mb-2">Do (Pelaksanaan)</div>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $laporan->pelaksanaan }}</p>
        </div>
        <div class="pt-card pt-card-pad">
            <div class="pt-judul mb-2">Penyebab</div>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $laporan->penyebab ?: 'Tidak ada catatan penyebab.' }}</p>
        </div>
    </div>

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">Action (Tindak Lanjut)</div>
        @if($laporan->barisTindakLanjut())
            <div style="overflow-x:auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b border-gray-200">
                            <th class="py-2 pr-3">Kegiatan</th>
                            <th class="py-2 pr-3">PIC</th>
                            <th class="py-2 pr-3">Batas waktu</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laporan->barisTindakLanjut() as $a)
                            <tr class="border-b border-gray-100 last:border-0">
                                <td class="py-2 pr-3 text-gray-900">{{ $a['kegiatan'] }}</td>
                                <td class="py-2 pr-3 text-gray-700">{{ $a['pic'] ?: '-' }}</td>
                                <td class="py-2 pr-3 text-gray-700">{{ $a['batas_waktu'] ?: '-' }}</td>
                                <td class="py-2 text-gray-700">{{ \App\Models\SasaranMutu::daftarStatusTindakLanjut()[$a['status'] ?? 'open'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500">Tidak ada tindak lanjut yang dicatat.</p>
        @endif
    </div>

    <div class="pt-card pt-card-pad">
        <div class="pt-judul mb-3">Pengesahan</div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <div class="text-xs text-gray-500">Dibuat oleh</div>
                <div class="text-gray-900 font-medium">{{ $laporan->nama }}</div>
                <div class="text-xs text-gray-500">
                    {{ $laporan->jabatan_pembuat ?: 'Staff / PIC / Supervisor' }} &middot; {{ $laporan->created_at->format('d M Y') }}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Diperiksa oleh</div>
                <div class="text-gray-900 font-medium">{{ $laporan->pemeriksa->name ?? 'Menunggu' }}</div>
                <div class="text-xs text-gray-500">
                    Kepala Departemen{{ $laporan->manager_pada ? ' · ' . $laporan->manager_pada->format('d M Y') : '' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Disetujui oleh</div>
                <div class="text-gray-900 font-medium">{{ $laporan->penyetuju->name ?? 'Menunggu' }}</div>
                <div class="text-xs text-gray-500">
                    Direktur{{ $laporan->direktur_pada ? ' · ' . $laporan->direktur_pada->format('d M Y') : '' }}
                </div>
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
