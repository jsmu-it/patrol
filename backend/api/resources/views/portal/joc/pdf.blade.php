<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 20mm 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .kop { border-bottom: 1.5px solid #111; padding-bottom: 8px; margin-bottom: 14px; }
        .judul { text-align: center; font-weight: bold; font-size: 13px; }
        .sub { text-align: center; font-size: 10px; }
        h2 { font-size: 11px; margin: 12px 0 5px; }
        table.isi { width: 100%; border-collapse: collapse; }
        table.isi td { padding: 3px 0; vertical-align: top; }
        .lbl { width: 26%; color: #333; }
        .sep { width: 3%; }
        .kotak { display: inline-block; width: 9px; height: 9px; border: 1px solid #111; text-align: center; line-height: 9px; font-size: 8px; margin-right: 4px; }
        .blok { border: 1px solid #111; padding: 6px; margin-top: 4px; min-height: 40px; }
        .catatan { margin-top: 12px; font-size: 8px; color: #444; font-style: italic; }
    </style>
</head>
<body>

<table class="kop" width="100%">
    <tr>
        <td width="25%"><strong style="font-size:9px">PT. JAYA SAKTI<br>MANDIRI UNGGUL</strong></td>
        <td width="50%">
            <div class="judul">JSMU OBSERVATION CARD</div>
            <div class="sub">KARTU PENGAMATAN JSMU</div>
        </td>
        <td width="25%" style="text-align:right;font-size:9px">{{ $kartu->nomor }}</td>
    </tr>
</table>

<h2>DATA PENGAMATAN</h2>
<table class="isi">
    <tr><td class="lbl">Tanggal</td><td class="sep">:</td><td>{{ $kartu->tanggal->format('d/m/Y') }}</td>
        <td class="lbl">Jam</td><td class="sep">:</td><td>{{ substr($kartu->jam, 0, 5) }}</td></tr>
    <tr><td class="lbl">Lokasi</td><td class="sep">:</td><td>{{ $kartu->lokasi }}</td>
        <td class="lbl">Project</td><td class="sep">:</td><td>{{ $kartu->project->name ?? '-' }}</td></tr>
    <tr><td class="lbl">Nama Pengamat</td><td class="sep">:</td><td colspan="4">{{ $kartu->nama }}</td></tr>
</table>

<h2>TEMUAN OBSERVASI</h2>
<div>
    <span class="kotak">{{ $kartu->jenis_temuan === 'tindakan_tidak_aman' ? 'x' : '' }}</span>Tindakan Tidak Aman
    &nbsp;&nbsp;
    <span class="kotak">{{ $kartu->jenis_temuan === 'kondisi_tidak_aman' ? 'x' : '' }}</span>Kondisi Tidak Aman
</div>

<h2>KATEGORI TEMUAN DARI AKTIFITAS OBSERVASI</h2>
<div>
    @foreach(\App\Models\JocCard::daftarKategori() as $n => [$label, $tanya])
        <span class="kotak">{{ in_array($n, $kartu->kategori ?? []) ? 'x' : '' }}</span>{{ $label }}&nbsp;&nbsp;
    @endforeach
    @if($kartu->kategori_lain)<br><small>Lain-lain: {{ $kartu->kategori_lain }}</small>@endif
</div>

<h2>RINCIAN TEMUAN PENGAMATAN</h2>
<div class="blok">{!! nl2br(e($kartu->rincian_temuan)) !!}</div>

<h2>RINCIAN TINDAKAN / REKOMENDASI TERHADAP TEMUAN</h2>
<div class="blok">{!! nl2br(e($kartu->rincian_tindakan)) !!}</div>

@if($kartu->catatan)
    <h2>CATATAN TAMBAHAN</h2>
    <div class="blok">{!! nl2br(e($kartu->catatan)) !!}</div>
@endif

<table class="isi" style="margin-top:14px">
    <tr>
        <td width="60%">
            <strong>Tindak lanjut HSE</strong><br>
            Status: {{ \App\Models\JocCard::daftarStatus()[$kartu->status] }}<br>
            {{ $kartu->tanggapan_hse ?: '-' }}
        </td>
        <td width="40%" style="text-align:center">
            <strong>TTD Pengamat</strong><br>
            @if($kartu->ttd)
                <img src="{{ $kartu->ttd }}" style="height:52px">
            @else
                <div style="height:52px"></div>
            @endif
            <div style="border-top:1px solid #111;padding-top:2px">{{ $kartu->nama }}</div>
        </td>
    </tr>
</table>

<p class="catatan">
    * Jika temuan bersifat emergency dan berpotensi terjadi kecelakaan saat proses pengamatan berlangsung,
    pengamat WAJIB menghentikan pekerjaan tersebut.
</p>
<p class="catatan">Dicetak otomatis oleh Portal Kerja JSMU pada {{ now()->format('d/m/Y H:i') }} WIB.</p>

</body>
</html>
