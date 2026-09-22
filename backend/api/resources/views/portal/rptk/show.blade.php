@extends('layouts.portal')
@section('title', 'RPTK ' . $rptk->nomor)

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('portal.rptk.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar RPTK</a>

    <div class="flex flex-wrap items-center justify-between gap-3 mt-2 mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-xl font-bold text-gray-900">{{ $rptk->nomor }}</h1>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $rptk->warnaStatus() }}">{{ $rptk->labelStatus() }}</span>
        </div>
        <a href="{{ route('portal.rptk.pdf', $rptk) }}" class="pt-btn pt-btn-garis pt-btn-kecil">Unduh PDF</a>
    </div>

    {{-- Tombol persetujuan --}}
    @if($bolehSetujui)
        <div class="pt-card pt-card-pad mb-4" style="border-color:#BFDBFE;background:#EFF6FF" x-data="{ tolak: false }">
            <div class="pt-judul mb-1">Permintaan ini menunggu keputusan Anda</div>
            <p class="text-xs text-gray-600 mb-3">
                Tahap: {{ $rptk->status === 'menunggu_hrga' ? 'Mengetahui (HR & GA Manager)' : 'Menyetujui (Direktur)' }}
            </p>
            <form action="{{ route('portal.rptk.putuskan', $rptk) }}" method="POST" class="space-y-3">
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

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">A. Permintaan</div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
            <div><dt class="text-gray-500 text-xs">Pemohon</dt><dd class="text-gray-900">{{ $rptk->nama }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Jumlah kebutuhan</dt><dd class="text-gray-900">{{ $rptk->jumlah }} orang</dd></div>
            <div><dt class="text-gray-500 text-xs">Divisi / Dept</dt><dd class="text-gray-900">{{ $rptk->divisi }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Lokasi kerja</dt><dd class="text-gray-900">{{ $rptk->lokasi_kerja }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Alasan permintaan</dt><dd class="text-gray-900">{{ \App\Models\RptkRequest::daftarAlasan()[$rptk->alasan] }}{{ $rptk->alasan_lain ? ' — '.$rptk->alasan_lain : '' }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Status karyawan</dt><dd class="text-gray-900">{{ \App\Models\RptkRequest::daftarStatusKaryawan()[$rptk->status_karyawan] }}{{ $rptk->status_karyawan_lain ? ' — '.$rptk->status_karyawan_lain : '' }}</dd></div>
            @if($rptk->ref_dokumen)<div><dt class="text-gray-500 text-xs">Ref. dokumen</dt><dd class="text-gray-900">{{ $rptk->ref_dokumen }}</dd></div>@endif
            @if($rptk->no_tanggal_dokumen)<div><dt class="text-gray-500 text-xs">No. &amp; tanggal dokumen</dt><dd class="text-gray-900">{{ $rptk->no_tanggal_dokumen }}</dd></div>@endif
        </dl>
    </div>

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">Spesifikasi Jabatan</div>
        <p class="text-sm font-semibold text-gray-900 mb-2">{{ $rptk->jabatan }}</p>
        <ol class="list-decimal pl-5 text-sm text-gray-700 space-y-1">
            @foreach(preg_split('/\r\n|\r|\n/', $rptk->uraian_tugas) as $baris)
                @if(trim($baris) !== '')<li>{{ trim($baris) }}</li>@endif
            @endforeach
        </ol>
    </div>

    <div class="pt-card pt-card-pad mb-4">
        <div class="pt-judul mb-3">B. Spesifikasi Tenaga Kerja</div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
            <div><dt class="text-gray-500 text-xs">Tinggi badan</dt><dd class="text-gray-900">{{ $rptk->tinggi_badan ?: '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Berat badan</dt><dd class="text-gray-900">{{ $rptk->berat_badan ?: '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Usia</dt><dd class="text-gray-900">{{ $rptk->usia ?: '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Jenis kelamin</dt><dd class="text-gray-900">{{ \App\Models\RptkRequest::daftarKelamin()[$rptk->jenis_kelamin] }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Pendidikan formal</dt><dd class="text-gray-900">{{ $rptk->pendidikan_formal ?: '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Keahlian khusus</dt><dd class="text-gray-900">{{ $rptk->keahlian_khusus ?: '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Pendidikan non formal</dt><dd class="text-gray-900">{{ $rptk->pendidikan_non_formal ?: '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs">Pengalaman</dt><dd class="text-gray-900">{{ $rptk->pengalaman ?: '—' }}</dd></div>
        </dl>
    </div>

    <div class="pt-card pt-card-pad">
        <div class="pt-judul mb-3">Rekomendasi &amp; Persetujuan</div>
        <p class="text-sm text-gray-900 mb-1">Sumber rekrutmen: {{ \App\Models\RptkRequest::daftarSumber()[$rptk->sumber] }}</p>
        <p class="text-sm text-gray-900 mb-3">Tanggal efektif bekerja: {{ $rptk->tanggal_efektif ?: '—' }}</p>
        @if($rptk->catatan)
            <p class="text-sm text-gray-700 whitespace-pre-line mb-3">{{ $rptk->catatan }}</p>
        @endif

        <div class="border-t border-gray-100 pt-3 text-sm space-y-2">
            <div class="flex justify-between gap-3">
                <span class="text-gray-500">Pemohon</span>
                <span class="text-gray-900">{{ $rptk->nama }} &middot; {{ $rptk->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between gap-3">
                <span class="text-gray-500">Mengetahui (HR &amp; GA Manager)</span>
                <span class="text-gray-900">{{ $rptk->hrga_pada ? ($rptk->hrga_oleh ? \App\Models\User::find($rptk->hrga_oleh)?->name : '') . ' · ' . $rptk->hrga_pada->format('d M Y') : 'Menunggu' }}</span>
            </div>
            <div class="flex justify-between gap-3">
                <span class="text-gray-500">Menyetujui (Direktur)</span>
                <span class="text-gray-900">{{ $rptk->direktur_pada ? ($rptk->direktur_oleh ? \App\Models\User::find($rptk->direktur_oleh)?->name : '') . ' · ' . $rptk->direktur_pada->format('d M Y') : 'Menunggu' }}</span>
            </div>
        </div>

        @if($rptk->status === 'ditolak' && $rptk->alasan_penolakan)
            <div class="mt-3 px-3 py-2 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                Ditolak: {{ $rptk->alasan_penolakan }}
            </div>
        @endif
    </div>
</div>
@endsection
