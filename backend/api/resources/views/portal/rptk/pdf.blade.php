<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .kop { border-bottom: 1.5px solid #111; padding-bottom: 8px; margin-bottom: 14px; }
        .kop td { vertical-align: top; }
        .judul { text-align: center; font-weight: bold; font-size: 13px; line-height: 1.4; }
        .kode { text-align: right; font-size: 9px; }
        h2 { font-size: 11px; margin: 14px 0 6px; }
        table.isi { width: 100%; border-collapse: collapse; }
        table.isi td { padding: 3px 0; vertical-align: top; }
        .lbl { width: 34%; color: #333; }
        .sep { width: 3%; }
        .kotak { display: inline-block; width: 9px; height: 9px; border: 1px solid #111; text-align: center; line-height: 9px; font-size: 8px; margin-right: 4px; }
        ol { margin: 4px 0 0 14px; padding: 0; }
        ol li { margin-bottom: 4px; text-align: justify; }
        table.ttd { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.ttd th, table.ttd td { border: 1px solid #111; padding: 6px; text-align: center; font-size: 9px; }
        table.ttd .ruang { height: 46px; }
    </style>
</head>
<body>

<table class="kop" width="100%">
    <tr>
        <td width="22%"><strong style="font-size:9px">PT. JAYA SAKTI<br>MANDIRI UNGGUL</strong></td>
        <td width="56%" class="judul">FORMULIR<br>RENCANA PERMINTAAN TENAGA KERJA<br>(RPTK)</td>
        <td width="22%" class="kode">HR/FM-04-01<br>{{ $rptk->nomor }}</td>
    </tr>
</table>

<h2>A. PERMINTAAN</h2>
<table class="isi">
    <tr>
        <td class="lbl">Nama</td><td class="sep">:</td><td>{{ $rptk->nama }}</td>
        <td class="lbl">Jumlah Kebutuhan Karyawan</td><td class="sep">:</td><td>{{ $rptk->jumlah }}</td>
    </tr>
    <tr>
        <td class="lbl">Divisi/Dept</td><td class="sep">:</td><td>{{ $rptk->divisi }}</td>
        <td class="lbl">Ref. Dokumen Dari</td><td class="sep">:</td><td>{{ $rptk->ref_dokumen ?: '' }}</td>
    </tr>
    <tr>
        <td class="lbl">Untuk Lokasi Kerja</td><td class="sep">:</td><td>{{ $rptk->lokasi_kerja }}</td>
        <td class="lbl">No. &amp; Tanggal Dokumen</td><td class="sep">:</td><td>{{ $rptk->no_tanggal_dokumen ?: '' }}</td>
    </tr>
</table>

<h2>ALASAN PERMINTAAN</h2>
<div>
    @foreach(\App\Models\RptkRequest::daftarAlasan() as $n => $l)
        <span class="kotak">{{ $rptk->alasan === $n ? 'x' : '' }}</span>{{ $l }}
        @if($n === 'lain' && $rptk->alasan_lain) : {{ $rptk->alasan_lain }} @endif
        &nbsp;&nbsp;
    @endforeach
</div>

<h2>STATUS KARYAWAN</h2>
<div>
    @foreach(\App\Models\RptkRequest::daftarStatusKaryawan() as $n => $l)
        <span class="kotak">{{ $rptk->status_karyawan === $n ? 'x' : '' }}</span>{{ $l }}
        @if($n === 'lain' && $rptk->status_karyawan_lain) : {{ $rptk->status_karyawan_lain }} @endif
        &nbsp;&nbsp;
    @endforeach
</div>

<h2>SPESIFIKASI JABATAN</h2>
<table class="isi">
    <tr><td class="lbl">Jabatan</td><td class="sep">:</td><td>{{ $rptk->jabatan }}</td></tr>
    <tr><td class="lbl">Uraian Tugas &amp; Tanggung Jawab</td><td class="sep">:</td><td></td></tr>
</table>
<ol>
    @foreach(preg_split('/\r\n|\r|\n/', $rptk->uraian_tugas) as $baris)
        @if(trim($baris) !== '')<li>{{ trim($baris) }}</li>@endif
    @endforeach
</ol>

<h2>B. SPESIFIKASI TENAGA KERJA YANG DIHARAPKAN</h2>
<table class="isi">
    <tr>
        <td class="lbl">Tinggi Badan</td><td class="sep">:</td><td>{{ $rptk->tinggi_badan ?: '' }}</td>
        <td class="lbl">Berat Badan</td><td class="sep">:</td><td>{{ $rptk->berat_badan ?: '' }}</td>
    </tr>
    <tr>
        <td class="lbl">Usia</td><td class="sep">:</td><td>{{ $rptk->usia ?: '' }}</td>
        <td class="lbl">Jenis Kelamin</td><td class="sep">:</td>
        <td>
            <span class="kotak">{{ $rptk->jenis_kelamin === 'laki' ? 'x' : '' }}</span>Laki-laki
            <span class="kotak">{{ $rptk->jenis_kelamin === 'perempuan' ? 'x' : '' }}</span>Perempuan
        </td>
    </tr>
    <tr>
        <td class="lbl">Pendidikan Formal</td><td class="sep">:</td><td>{{ $rptk->pendidikan_formal ?: '' }}</td>
        <td class="lbl">Keahlian Khusus</td><td class="sep">:</td><td>{{ $rptk->keahlian_khusus ?: '' }}</td>
    </tr>
    <tr>
        <td class="lbl">Pendidikan Non Formal</td><td class="sep">:</td><td>{{ $rptk->pendidikan_non_formal ?: '' }}</td>
        <td class="lbl">Pengalaman</td><td class="sep">:</td><td>{{ $rptk->pengalaman ?: '' }}</td>
    </tr>
</table>

<h2>REKOMENDASI SUMBER REKRUTMENT</h2>
<div>
    <span class="kotak">{{ $rptk->sumber === 'eksternal' ? 'x' : '' }}</span>Eksternal
    &nbsp;&nbsp;
    <span class="kotak">{{ $rptk->sumber === 'internal' ? 'x' : '' }}</span>Internal (Mutasi, Demosi, Promosi)
</div>

<p style="margin-top:8px"><strong>Catatan/Keterangan</strong><br>{{ $rptk->catatan ?: '-' }}</p>
<p>Tanggal Efektif Bekerja : {{ $rptk->tanggal_efektif ?: '-' }}</p>

<table class="ttd">
    <tr><th colspan="3">APPROVAL</th></tr>
    <tr><th>Pemohon</th><th>Mengetahui</th><th>Menyetujui</th></tr>
    <tr class="ruang">
        <td>{{ $rptk->nama }}<br><small>{{ $rptk->created_at->format('d/m/Y') }}</small></td>
        <td>
            @if($rptk->hrga_pada)
                {{ \App\Models\User::find($rptk->hrga_oleh)?->name }}<br><small>{{ $rptk->hrga_pada->format('d/m/Y') }}</small>
            @else
                <small>menunggu</small>
            @endif
        </td>
        <td>
            @if($rptk->direktur_pada)
                {{ \App\Models\User::find($rptk->direktur_oleh)?->name }}<br><small>{{ $rptk->direktur_pada->format('d/m/Y') }}</small>
            @else
                <small>menunggu</small>
            @endif
        </td>
    </tr>
    <tr><td>Manager</td><td>HR &amp; GA Manager</td><td>Direktur</td></tr>
</table>

<p style="margin-top:10px;font-size:8px;color:#555">
    Status dokumen saat dicetak: {{ $rptk->labelStatus() }}. Dicetak otomatis oleh Portal Kerja JSMU
    pada {{ now()->format('d/m/Y H:i') }} WIB.
</p>

</body>
</html>
