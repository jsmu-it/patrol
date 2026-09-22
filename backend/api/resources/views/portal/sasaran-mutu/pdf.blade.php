<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }

        table { border-collapse: collapse; width: 100%; }
        .kop td { border: 1px solid #111; padding: 4px 6px; vertical-align: middle; }
        .kop .judul { text-align: center; font-weight: bold; font-size: 13px; }
        .kop .perusahaan { text-align: center; font-size: 7px; font-weight: bold; }
        .kop .meta { font-size: 8px; padding: 2px 6px; }

        .bar td { border: 1px solid #111; padding: 3px 6px; font-weight: bold; background: #D9D9D9; }
        .isi td, .isi th { border: 1px solid #111; padding: 4px 6px; vertical-align: top; }
        .isi th { background: #F2F2F2; font-size: 8px; text-align: center; }
        .kosong td { height: 26px; }
        .tengah { text-align: center; }

        .ttd td { border: 1px solid #111; padding: 4px 6px; text-align: center; vertical-align: top; }
        .ttd .ruang { height: 38px; }
        .ttd .peran { font-weight: bold; font-size: 8px; }
        .ttd .tgl { font-size: 7px; color: #555; }
        .catatan { margin-top: 8px; font-size: 7px; color: #444; font-style: italic; }
    </style>
</head>
<body>

{{-- Kop dokumen --}}
<table class="kop">
    <tr>
        <td width="22%" class="tengah">
            <img src="{{ public_path('images/admin-logo.png') }}" style="height:36px"><br>
            <span class="perusahaan">PT. JAYA SAKTI MANDIRI UNGGUL</span>
        </td>
        <td width="48%" class="judul">LAPORAN PENCAPAIAN SASARAN MUTU</td>
        <td width="30%" style="padding:0">
            <table>
                <tr><td class="meta" width="40%" style="border:0;border-bottom:1px solid #111">No Doc</td>
                    <td class="meta" style="border:0;border-bottom:1px solid #111"><strong>: {{ \App\Models\SasaranMutu::KODE_FORMULIR }}</strong></td></tr>
                <tr><td class="meta" style="border:0;border-bottom:1px solid #111">Rev</td>
                    <td class="meta" style="border:0;border-bottom:1px solid #111">: {{ \App\Models\SasaranMutu::REVISI }}</td></tr>
                <tr><td class="meta" style="border:0">Tgl Efektif</td>
                    <td class="meta" style="border:0">: Desember 2021</td></tr>
            </table>
        </td>
    </tr>
</table>

{{-- Bulan & departemen --}}
<table class="isi" style="margin-top:6px">
    <tr>
        <td width="10%" style="background:#D9D9D9;font-weight:bold">Bulan</td>
        <td width="40%">{{ $laporan->labelBulan() }}</td>
        <td width="20%" style="background:#D9D9D9;font-weight:bold" class="tengah">Dept / Sub Dept</td>
        <td width="30%">{{ $laporan->labelDepartemen() }}</td>
    </tr>
</table>

{{-- PLAN --}}
<table class="bar" style="margin-top:6px"><tr><td>PLAN (PERENCANAAN)</td></tr></table>
<table class="isi">
    <tr>
        <th width="8%" rowspan="2">No</th>
        <th width="34%" rowspan="2">Sasaran Strategi</th>
        <th width="34%" rowspan="2">Ukuran Strategi</th>
        <th colspan="2">Target</th>
    </tr>
    <tr><th width="12%">Ukuran</th><th width="12%">Unit</th></tr>
    @foreach($laporan->barisSasaran() as $i => $b)
        <tr>
            <td class="tengah">{{ $i + 1 }}</td>
            <td>{{ $b['sasaran'] }}</td>
            <td>{{ $b['ukuran'] ?? '' }}</td>
            <td class="tengah">{{ $b['target_ukuran'] ?? '' }}</td>
            <td class="tengah">{{ $b['target_unit'] ?? '' }}</td>
        </tr>
    @endforeach
</table>

{{-- DO --}}
<table class="bar" style="margin-top:6px"><tr><td>DO (PELAKSANAAN)</td></tr></table>
<table class="isi">
    <tr><td style="height:46px">{!! nl2br(e($laporan->pelaksanaan)) !!}</td></tr>
</table>

{{-- CHECK --}}
<table class="bar" style="margin-top:6px"><tr><td>CHECK (PEMERIKSAAN)</td></tr></table>
<table class="bar"><tr><td>HASIL</td></tr></table>
<table class="isi">
    <tr>
        <th width="8%">No</th>
        <th width="34%">Sasaran Strategi</th>
        <th width="34%">Ukuran Strategi</th>
        <th width="12%">Hasil</th>
        <th width="12%">Pencapaian</th>
    </tr>
    @foreach($laporan->barisSasaran() as $i => $b)
        <tr>
            <td class="tengah">{{ $i + 1 }}</td>
            <td>{{ $b['sasaran'] }}</td>
            <td>{{ $b['ukuran'] ?? '' }}</td>
            <td class="tengah">{{ $b['realisasi'] ?? '' }}</td>
            <td class="tengah">{{ $b['pencapaian'] ?? '' }}</td>
        </tr>
    @endforeach
</table>

<table class="bar" style="margin-top:6px"><tr><td>PENYEBAB</td></tr></table>
<table class="isi">
    <tr><td style="height:38px">{!! nl2br(e($laporan->penyebab ?: '-')) !!}</td></tr>
</table>

{{-- ACTION --}}
<table class="bar" style="margin-top:6px"><tr><td>ACTION (TINDAK LANJUT)</td></tr></table>
<table class="isi">
    <tr>
        <th width="8%">No</th>
        <th width="48%">Kegiatan</th>
        <th width="16%">PIC</th>
        <th width="16%">Batas Waktu</th>
        <th width="12%">Status</th>
    </tr>
    @forelse($laporan->barisTindakLanjut() as $i => $a)
        <tr>
            <td class="tengah">{{ $i + 1 }}</td>
            <td>{{ $a['kegiatan'] }}</td>
            <td>{{ $a['pic'] ?? '' }}</td>
            <td>{{ $a['batas_waktu'] ?? '' }}</td>
            <td class="tengah">{{ \App\Models\SasaranMutu::daftarStatusTindakLanjut()[$a['status'] ?? 'open'] ?? '' }}</td>
        </tr>
    @empty
        <tr class="kosong"><td colspan="5"></td></tr>
    @endforelse
</table>

@if($laporan->status === 'ditolak' && $laporan->alasan_penolakan)
    <table class="isi" style="margin-top:6px">
        <tr><td><strong>Ditolak:</strong> {{ $laporan->alasan_penolakan }}</td></tr>
    </table>
@endif

{{-- Pengesahan --}}
{{-- Baris tanggal sekaligus penanda cetak, agar laporan pendek tetap satu halaman. --}}
<table style="margin:6px 0 2px">
    <tr>
        <td style="font-weight:bold">Jakarta, {{ $laporan->tanggal->format('d/m/Y') }}</td>
        <td class="catatan" style="margin:0;text-align:right">
            {{ $laporan->nomor }} &middot; {{ $laporan->labelStatus() }}
            &middot; dicetak otomatis oleh Portal Kerja JSMU {{ now()->format('d/m/Y H:i') }} WIB
        </td>
    </tr>
</table>
<table class="ttd">
    <tr>
        <td width="34%" class="peran">DIBUAT OLEH,</td>
        <td width="33%" class="peran">DIPERIKSA OLEH,</td>
        <td width="33%" class="peran">DISETUJUI OLEH,</td>
    </tr>
    <tr>
        <td class="ruang"></td>
        <td class="ruang"></td>
        <td class="ruang"></td>
    </tr>
    <tr>
        <td>
            <strong>{{ $laporan->nama }}</strong><br>
            ( {{ $laporan->jabatan_pembuat ?: 'Staff / PIC / Supervisor' }} )<br>
            <span class="tgl">{{ $laporan->created_at->format('d/m/Y') }}</span>
        </td>
        <td>
            <strong>{{ $laporan->pemeriksa->name ?? '' }}</strong><br>
            KEPALA DEPARTEMEN<br>
            <span class="tgl">{{ $laporan->manager_pada?->format('d/m/Y') ?: 'menunggu' }}</span>
        </td>
        <td>
            <strong>{{ $laporan->penyetuju->name ?? '' }}</strong><br>
            DIREKTUR<br>
            <span class="tgl">{{ $laporan->direktur_pada?->format('d/m/Y') ?: 'menunggu' }}</span>
        </td>
    </tr>
</table>


</body>
</html>
