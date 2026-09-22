<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 10mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }

        table { border-collapse: collapse; width: 100%; }
        .kop td { border: 1px solid #111; padding: 5px 7px; vertical-align: middle; }
        .kop .judul { text-align: center; font-weight: bold; font-size: 13px; line-height: 1.3; }
        .kop .perusahaan { text-align: center; font-size: 7.5px; font-weight: bold; }
        .kop .kode { text-align: right; font-size: 8.5px; font-weight: bold; }
        .kop .meta { font-size: 8.5px; padding: 2px 6px; border: 0; }

        .isi td, .isi th { border: 1px solid #111; padding: 4px 6px; vertical-align: top; }
        .isi th { background: #F2F2F2; font-size: 8.5px; text-align: center; }
        .tengah { text-align: center; }
        .kosong td { height: 20px; }

        .ttd { margin-top: 14px; }
        .ttd td { text-align: center; vertical-align: bottom; font-size: 9px; }
        .ttd .ruang { height: 46px; }
        .ttd .nama { padding-top: 2px; }
        .catatan { margin-top: 10px; font-size: 7px; color: #444; font-style: italic; }
    </style>
</head>
<body>

{{-- Kop dokumen --}}
<table class="kop">
    <tr>
        <td width="26%" class="tengah">
            <img src="{{ public_path('images/admin-logo.png') }}" style="height:30px"><br>
            <span class="perusahaan">PT. JAYA SAKTI MANDIRI UNGGUL</span>
        </td>
        <td width="40%" class="judul">PERMINTAAN BARANG<br>GUDANG (PBG)</td>
        <td width="34%" style="padding:0">
            <table>
                <tr><td class="kode" colspan="2" style="border-bottom:1px solid #111;padding:3px 6px">{{ \App\Models\PbgRequest::KODE_FORMULIR }}</td></tr>
                <tr><td class="meta" width="35%" style="border-bottom:1px solid #111">NO. PBG</td>
                    <td class="meta" style="border-bottom:1px solid #111"><strong>: {{ $pbg->nomor }}</strong></td></tr>
                <tr><td class="meta">TGL. PBG</td>
                    <td class="meta">: {{ $pbg->tgl_pbg->format('d/m/Y') }}</td></tr>
            </table>
        </td>
    </tr>
</table>

{{-- Identitas pemohon --}}
<table class="isi" style="margin-top:5px">
    <tr>
        <td width="18%">NAMA PEMOHON</td><td width="32%">: {{ $pbg->nama }}</td>
        <td width="18%">UNIT KERJA</td><td width="32%">: {{ $pbg->unit_kerja ?: '-' }}</td>
    </tr>
    <tr>
        <td>DEPARTEMEN</td><td>: {{ $pbg->departemen }}</td>
        <td>TGL. PENGGUNAAN</td><td>: {{ $pbg->tgl_penggunaan?->format('d/m/Y') ?: '-' }}</td>
    </tr>
</table>

{{-- Daftar barang --}}
<table class="isi" style="margin-top:5px">
    <tr>
        <th width="5%">NO</th>
        <th width="12%">KODE</th>
        <th width="43%">NAMA BARANG DAN SPESIFIKASI</th>
        <th width="9%">JUMLAH</th>
        <th width="10%">SATUAN</th>
        <th width="21%">KETERANGAN</th>
    </tr>
    @foreach($pbg->barisBarang() as $i => $b)
        <tr>
            <td class="tengah">{{ $i + 1 }}</td>
            <td>{{ $b['kode'] ?? '' }}</td>
            <td>{{ $b['nama'] }}</td>
            <td class="tengah">{{ $b['jumlah'] ?? '' }}</td>
            <td class="tengah">{{ $b['satuan'] ?? '' }}</td>
            <td>{{ $b['keterangan'] ?? '' }}</td>
        </tr>
    @endforeach
    {{-- Baris sisa dibiarkan kosong seperti formulir cetaknya --}}
    @for($i = $pbg->jumlahJenis(); $i < 8; $i++)
        <tr class="kosong"><td class="tengah">{{ $i + 1 }}</td><td></td><td></td><td></td><td></td><td></td></tr>
    @endfor
</table>

@if($pbg->catatan_gudang)
    <table class="isi" style="margin-top:5px">
        <tr><td><strong>Catatan gudang:</strong> {{ $pbg->catatan_gudang }}</td></tr>
    </table>
@endif

@if($pbg->status === 'ditolak' && $pbg->alasan_penolakan)
    <table class="isi" style="margin-top:5px">
        <tr><td><strong>Ditolak:</strong> {{ $pbg->alasan_penolakan }}</td></tr>
    </table>
@endif

{{-- Pengesahan --}}
<p style="margin:10px 0 2px">Jakarta, {{ \App\Support\TanggalIndonesia::panjang($pbg->tgl_pbg) }}</p>
<table class="ttd">
    <tr>
        <td width="25%">Diajukan oleh,</td>
        <td width="25%">Menyetujui,</td>
        <td width="25%">Mengetahui,</td>
        <td width="25%">Diterima,</td>
    </tr>
    <tr><td class="ruang"></td><td class="ruang"></td><td class="ruang"></td><td class="ruang"></td></tr>
    <tr>
        <td class="nama">( {{ $pbg->nama }} )</td>
        <td class="nama">( {{ $pbg->kepalaBagian->name ?? '' }} )</td>
        <td class="nama">( {{ $pbg->kepala_gudang ?: '' }} )</td>
        <td class="nama">( {{ $pbg->petugasGudang->name ?? '' }} )</td>
    </tr>
    <tr>
        <td style="font-size:8px;color:#555">Pemohon</td>
        <td style="font-size:8px;color:#555">Kepala Bagian</td>
        <td style="font-size:8px;color:#555">Kep. Gudang</td>
        <td style="font-size:8px;color:#555">Petugas Gudang</td>
    </tr>
</table>

<p class="catatan">
    {{ $pbg->nomor }} &middot; {{ $pbg->labelStatus() }}
    &middot; dicetak otomatis oleh Portal Kerja JSMU {{ now()->format('d/m/Y H:i') }} WIB.
</p>

</body>
</html>
