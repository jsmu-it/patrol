@extends('layouts.company_profile')

@section('title', 'Layanan — PT. Jaya Sakti Mandiri Unggul')
@section('description', 'Lima lini layanan pengamanan: tenaga pengamanan, teknologi, pelatihan, konsultansi risiko, dan unit K-9.')

@section('content')
@php
    $baseLines = [
        ['security-guards','Tenaga Pengamanan','Penempatan anggota Satpam bersertifikat untuk penjagaan, pengawalan, pengaturan akses, dan pengendalian lalu lintas di lokasi klien. Setiap anggota terdaftar, berseragam sesuai ketentuan, dan diawasi supervisor lapangan.',['Penjagaan pos dan pintu masuk','Pengaturan akses tamu dan kendaraan','Patroli terjadwal dengan bukti titik','Pengawalan aset dan uang tunai','Laporan kejadian harian'],'Kawasan industri, perkantoran, pergudangan, properti hunian'],
        ['technology','Teknologi Pengamanan','Perangkat dan sistem yang membuat pengamanan bisa diperiksa, bukan sekadar dipercaya. Termasuk sistem absensi dan patroli digital yang kami kembangkan dan pakai sendiri.',['Absensi anggota dengan verifikasi lokasi','Patroli berbasis QR di tiap titik','CCTV dan pemantauan terpusat','Kontrol akses pintu dan gerbang','Laporan periodik untuk klien'],'Klien yang butuh bukti kehadiran dan rekam jejak patroli'],
        ['training','Pelatihan &amp; Pendidikan','Penyegaran berkala agar kemampuan anggota tidak berhenti di sertifikat awal. Materi disesuaikan dengan risiko di lokasi penempatan.',['Penyegaran Gada Pratama','Bela diri dan pengendalian massa','Penanganan kebakaran awal','Pertolongan pertama','Tanggap darurat dan evakuasi'],'Perusahaan yang ingin meningkatkan mutu anggota di lokasinya'],
        ['consultancy','Konsultansi &amp; Risiko','Kajian sebelum penempatan: di mana titik rawan, berapa anggota yang benar-benar diperlukan, dan prosedur apa yang harus ada.',['Kajian kerawanan lokasi','Perhitungan kebutuhan personel','Penyusunan prosedur pengamanan','Audit penerapan di lapangan','Rekomendasi perbaikan'],'Lokasi baru, perluasan area, atau setelah terjadi insiden'],
        ['k9','Unit K-9','Anjing terlatih beserta penanganya untuk tugas yang tidak bisa dikerjakan pengamanan konvensional.',['Deteksi bahan peledak','Deteksi narkotika','Patroli area luas','Pendukung pengamanan acara'],'Kawasan industri luas, pelabuhan, acara berisiko tinggi'],
    ];
@endphp

@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-layanan.jpg'))
        ? asset('assets/dummy/pagehead-layanan.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Layanan</p>
        <h1 class="jsm-h1">Layanan</h1>
        <p>Lima lini yang kami kerjakan. Tiap lini punya cakupan pekerjaan yang jelas supaya lingkup penawaran tidak bias.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container">
    @if($services->isNotEmpty())
        @foreach($services as $i => $service)
        <div id="{{ $service->slug }}" style="scroll-margin-top:96px;padding-bottom:56px;margin-bottom:56px;border-bottom:1px solid var(--line)">
            <div class="jsm-grid2" style="align-items:start">
                <div style="{{ $i % 2 == 1 ? 'order:2' : '' }}">
                    <span class="jsm-label">{{ $i + 1 }} dari 5</span>
                    <h2 class="jsm-h1" style="margin:10px 0 16px;font-size:30px">{{ $service->title }}</h2>
                    <div class="jsm-body jsm-muted">{!! $service->full_description ?: $service->short_description !!}</div>
                    <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-secondary" style="margin-top:24px">Tanyakan layanan ini</a>
                </div>
                <div style="{{ $i % 2 == 1 ? 'order:1' : '' }}">
                    @if($service->image)
                        <div class="jsm-imgbox"><img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->title }}" loading="lazy" width="800" height="500" style="display:block;width:100%;height:300px;object-fit:cover"></div>
                    @else
                        <div class="jsm-imgbox jsm-imgbox-empty" style="min-height:260px">Foto layanan belum diunggah</div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    @else
        @foreach($baseLines as $i => $line)
        <div id="{{ $line[0] }}" style="scroll-margin-top:96px;padding-bottom:48px;margin-bottom:48px;border-bottom:1px solid var(--line)">
            <div class="jsm-grid2" style="align-items:start">
                <div>
                    <span class="jsm-label">{{ $i + 1 }} dari 5</span>
                    <h2 class="jsm-h1" style="margin:10px 0 16px;font-size:30px">{!! $line[1] !!}</h2>
                    <p class="jsm-muted" style="max-width:60ch">{!! $line[2] !!}</p>
                    <p class="jsm-label" style="margin-top:24px">Cocok untuk</p>
                    <p style="font-size:14.5px;margin-top:4px">{{ $line[4] }}</p>
                </div>
                <div>
                    <div class="jsm-card jsm-card-pad">
                        <span class="jsm-label">Cakupan pekerjaan</span>
                        <ul style="margin:14px 0 0;padding-left:18px;font-size:14.5px;color:var(--muted)">
                            @foreach($line[3] as $item)<li style="margin-bottom:7px">{{ $item }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        <p class="jsm-muted" style="font-size:14px">Rincian tiap layanan dapat diperbarui melalui panel admin.</p>
    @endif
    </div>
</section>

<section class="jsm-cta">
    <div class="jsm-container"><div class="jsm-cta-in">
        <div><h2>Belum yakin lini mana yang sesuai?</h2><p>Sampaikan kondisi lokasi Anda, kami bantu tentukan lingkupnya.</p></div>
        <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-light">Minta Penawaran</a>
    </div>
    </div>
</section>
@endsection
