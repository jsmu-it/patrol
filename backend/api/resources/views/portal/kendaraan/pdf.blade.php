<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }

        .kop { margin-bottom: 10px; }
        .kop .perusahaan { font-size: 9px; font-weight: bold; letter-spacing: .04em; }
        .judul { text-align: center; font-weight: bold; font-size: 13px; letter-spacing: .06em; margin: 10px 0 14px; }

        table { border-collapse: collapse; width: 100%; }
        .isi td { padding: 5px 0; vertical-align: bottom; }
        .isi .lbl { width: 24%; }
        .isi .sep { width: 3%; }
        .isi .garis { border-bottom: 1px solid #111; }

        .ttd { margin-top: 22px; }
        .ttd td { text-align: center; vertical-align: bottom; font-size: 10px; }
        .ttd .ruang { height: 52px; }
        .ttd .nama { padding-top: 2px; }

        .ga { margin-top: 18px; border-top: 1px dashed #111; padding-top: 10px; }
        .ga .judul-ga { font-weight: bold; margin-bottom: 6px; }
        .catatan { margin-top: 14px; font-size: 7.5px; color: #444; font-style: italic; }
    </style>
</head>
<body>

<table class="kop">
    <tr>
        <td width="14%"><img src="{{ public_path('images/admin-logo.png') }}" style="height:34px"></td>
        <td class="perusahaan">PT. JAYA SAKTI MANDIRI UNGGUL</td>
        <td width="26%" style="text-align:right;font-size:9px">{{ $permintaan->nomor }}</td>
    </tr>
</table>

<div class="judul">FORM PERMINTAAN KENDARAAN</div>

<table class="isi">
    <tr><td class="lbl">NAMA</td><td class="sep">:</td><td class="garis">{{ $permintaan->nama }}</td></tr>
    <tr><td class="lbl">BAGIAN</td><td class="sep">:</td><td class="garis">{{ $permintaan->bagian }}</td></tr>
    <tr><td class="lbl">HARI / TANGGAL</td><td class="sep">:</td><td class="garis">{{ $permintaan->labelHariTanggal() }}</td></tr>
    <tr><td class="lbl">JAM</td><td class="sep">:</td><td class="garis">{{ $permintaan->labelJam() }}</td></tr>
    <tr><td class="lbl" style="vertical-align:top">KEPERLUAN</td><td class="sep" style="vertical-align:top">:</td>
        <td class="garis">{!! nl2br(e($permintaan->keperluan)) !!}</td></tr>
</table>

<table class="ttd">
    <tr>
        <td width="33%">Pemohon,</td>
        <td width="34%">Atasan Langsung,</td>
        <td width="33%">Bagian GA,</td>
    </tr>
    <tr><td class="ruang"></td><td class="ruang"></td><td class="ruang"></td></tr>
    <tr>
        <td class="nama">( {{ $permintaan->nama }} )</td>
        <td class="nama">( {{ $permintaan->atasan->name ?? '' }} )</td>
        <td class="nama">( {{ $permintaan->petugasGa->name ?? '' }} )</td>
    </tr>
    <tr>
        <td style="font-size:8px;color:#555">{{ $permintaan->created_at->format('d/m/Y') }}</td>
        <td style="font-size:8px;color:#555">{{ $permintaan->atasan_pada?->format('d/m/Y') ?: 'menunggu' }}</td>
        <td style="font-size:8px;color:#555">{{ $permintaan->ga_pada?->format('d/m/Y') ?: 'menunggu' }}</td>
    </tr>
</table>

<div class="ga">
    <div class="judul-ga">CATATAN GA :</div>
    <table class="isi">
        <tr><td class="lbl">DRIVER</td><td class="sep">:</td><td class="garis">{{ $permintaan->driver ?: '' }}</td></tr>
        <tr><td class="lbl">NO. POLISI</td><td class="sep">:</td><td class="garis">{{ $permintaan->no_polisi ?: '' }}</td></tr>
        @if($permintaan->catatan_ga)
            <tr><td class="lbl">KETERANGAN</td><td class="sep">:</td><td class="garis">{{ $permintaan->catatan_ga }}</td></tr>
        @endif
    </table>
</div>

@if($permintaan->status === 'ditolak' && $permintaan->alasan_penolakan)
    <p style="margin-top:10px"><strong>Ditolak:</strong> {{ $permintaan->alasan_penolakan }}</p>
@endif

<p class="catatan">
    {{ $permintaan->nomor }} &middot; {{ $permintaan->labelStatus() }}
    &middot; dicetak otomatis oleh Portal Kerja JSMU {{ now()->format('d/m/Y H:i') }} WIB.
</p>

</body>
</html>
