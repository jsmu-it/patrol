@extends('layouts.company_profile')

@section('title', 'Kebijakan Privasi — PT. Jaya Sakti Mandiri Unggul')

@section('content')
@php
    $company = \App\Models\Setting::get('company_name', 'PT. Jaya Sakti Mandiri Unggul');
    $mail    = \App\Models\Setting::get('footer_email', 'info@jsmuguard.com');
@endphp

@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-profil.jpg'))
        ? asset('assets/dummy/pagehead-profil.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Kebijakan Privasi</p>
        <h1 class="jsm-h1">Kebijakan Privasi</h1>
        <p>Bagaimana data pelamar, klien, dan anggota kami kumpulkan, gunakan, dan simpan.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container" style="max-width:760px">
        <div class="jsm-body">
            <h2>1. Data yang kami kumpulkan</h2>
            <p>Melalui situs ini kami mengumpulkan data yang Anda kirimkan sendiri:</p>
            <ul>
                <li><strong>Permintaan layanan:</strong> nama, surel, nama perusahaan, dan keterangan kebutuhan pengamanan.</li>
                <li><strong>Lamaran kerja:</strong> data pribadi, riwayat pendidikan dan pekerjaan, dokumen identitas, serta berkas lamaran yang Anda unggah.</li>
            </ul>

            <h2>2. Penggunaan data</h2>
            <p>Data permintaan layanan dipakai untuk menyusun dan mengirimkan penawaran. Data lamaran dipakai untuk proses seleksi dan, bila diterima, menjadi dasar pencatatan kepegawaian. Kami tidak memperjualbelikan data Anda kepada pihak mana pun.</p>

            <h2>3. Data anggota dalam sistem absensi</h2>
            <p>Anggota yang bertugas menggunakan aplikasi absensi dan patroli. Sistem mencatat waktu, titik lokasi, dan foto saat absensi sebagai bukti kehadiran di lokasi penempatan. Data ini dipakai untuk keperluan operasional dan pelaporan kepada klien, serta dapat diakses oleh admin proyek yang berwenang.</p>

            <h2>4. Penyimpanan dan keamanan</h2>
            <p>Data disimpan pada peladen yang kami kelola dengan akses terbatas menurut peran pengguna. Sambungan situs dan aplikasi menggunakan enkripsi HTTPS. Berkas lamaran yang tidak lolos seleksi disimpan sebagai arsip kandidat kecuali Anda meminta penghapusan.</p>

            <h2>5. Hak Anda</h2>
            <p>Anda berhak meminta salinan, koreksi, atau penghapusan data pribadi Anda yang kami simpan. Permintaan dapat dikirim ke <a href="mailto:{{ $mail }}">{{ $mail }}</a> dan kami tanggapi pada hari kerja.</p>

            <h2>6. Perubahan kebijakan</h2>
            <p>Kebijakan ini dapat diperbarui sewaktu-waktu. Versi yang berlaku adalah yang tercantum di halaman ini.</p>

            <hr class="jsm-hr">
            <p class="jsm-muted" style="font-size:14px">Ditetapkan oleh {{ $company }}. Pertanyaan mengenai kebijakan ini dapat disampaikan melalui <a href="{{ route('contact') }}">halaman kontak</a>.</p>
        </div>
    </div>
</section>
@endsection
