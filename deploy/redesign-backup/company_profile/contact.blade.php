@extends('layouts.company_profile')

@section('title', 'Kontak — PT. Jaya Sakti Mandiri Unggul')
@section('description', 'Ajukan permintaan penawaran layanan pengamanan.')

@section('content')
@php
    $addr  = \App\Models\Setting::get('footer_address', 'Jakarta, Indonesia');
    $mail  = \App\Models\Setting::get('footer_email', 'info@jsmuguard.com');
    $phone = \App\Models\Setting::get('footer_phone');
    $wa    = \App\Models\Setting::get('social_whatsapp');
    $hours = \App\Models\Setting::get('office_hours', 'Senin–Jumat, 08.00–17.00 WIB');
@endphp

@php
    $headImg = file_exists(public_path('assets/dummy/pagehead-kontak.jpg'))
        ? asset('assets/dummy/pagehead-kontak.jpg') : null;
@endphp
<div class="jsm-pagehead" @if($headImg) style="background-image:url('{{ $headImg }}')" @endif>
    <div class="jsm-container">
        <p class="jsm-crumb"><a href="{{ route('home') }}">Beranda</a> &rsaquo; Kontak</p>
        <h1 class="jsm-h1">Minta Penawaran</h1>
        <p>Semakin lengkap keterangan lokasi yang Anda sampaikan, semakin cepat penawaran kami siapkan.</p>
    </div>
</div>

<section class="jsm-sec">
    <div class="jsm-container">
        <div class="jsm-withside">
            <div>
                @if(session('success'))
                    <div class="jsm-alert jsm-alert-ok" role="status">
                        <span class="jsm-alert-t">Permintaan terkirim</span>
                        {{ session('success') }} Kami menghubungi kembali pada hari kerja berikutnya.
                    </div>
                @endif
                @if($errors->any())
                    <div class="jsm-alert jsm-alert-err" role="alert">
                        <span class="jsm-alert-t">Ada {{ $errors->count() }} isian yang perlu diperbaiki</span>
                        Periksa keterangan merah di bawah setiap kolom.
                    </div>
                @endif

                <form action="{{ route('contact.send') }}" method="POST" class="jsm-card jsm-card-pad" novalidate>
                    @csrf
                    <fieldset class="jsm-fieldset">
                        <legend class="jsm-legend">Keterangan pemohon</legend>

                        <div class="jsm-field">
                            <label for="name">Nama lengkap <span class="jsm-req">*</span></label>
                            <input type="text" id="name" name="name" class="jsm-input" value="{{ old('name') }}" required
                                   @error('name') aria-invalid="true" aria-describedby="err-name" @enderror>
                            @error('name')<span class="jsm-err" id="err-name" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="jsm-grid2" style="gap:0 24px">
                            <div class="jsm-field">
                                <label for="email">Surel <span class="jsm-req">*</span></label>
                                <input type="email" id="email" name="email" class="jsm-input" value="{{ old('email') }}" required
                                       @error('email') aria-invalid="true" aria-describedby="err-email" @enderror>
                                @error('email')<span class="jsm-err" id="err-email" role="alert">{{ $message }}</span>@enderror
                            </div>
                            <div class="jsm-field">
                                <label for="subject">Perusahaan / instansi <span class="jsm-req">*</span></label>
                                <input type="text" id="subject" name="subject" class="jsm-input" value="{{ old('subject') }}" required
                                       @error('subject') aria-invalid="true" aria-describedby="err-subject" @enderror>
                                <span class="jsm-hint">Dipakai sebagai judul permintaan.</span>
                                @error('subject')<span class="jsm-err" id="err-subject" role="alert">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="jsm-fieldset" style="margin-top:32px">
                        <legend class="jsm-legend">Kebutuhan pengamanan</legend>
                        <div class="jsm-field">
                            <label for="message">Keterangan kebutuhan <span class="jsm-req">*</span></label>
                            <textarea id="message" name="message" rows="7" class="jsm-input" required
                                      placeholder="Contoh:&#10;Lokasi: kawasan pergudangan, Bekasi&#10;Jumlah titik: 3 pos&#10;Jenis layanan: penjagaan 24 jam&#10;Rencana mulai: awal bulan depan"
                                      @error('message') aria-invalid="true" aria-describedby="err-message" @enderror>{{ old('message') }}</textarea>
                            <span class="jsm-hint">Sebutkan lokasi, jumlah titik penjagaan, jenis layanan, dan rencana waktu mulai.</span>
                            @error('message')<span class="jsm-err" id="err-message" role="alert">{{ $message }}</span>@enderror
                        </div>
                    </fieldset>

                    <button type="submit" class="jsm-btn jsm-btn-primary" style="width:100%;margin-top:8px"
                            x-data @click="$el.setAttribute('aria-disabled','true'); $el.textContent='Mengirim…'; $el.form.submit()">
                        Kirim Permintaan
                    </button>
                </form>
            </div>

            <aside>
                <div class="jsm-card jsm-card-pad">
                    <span class="jsm-label">Kantor</span>
                    <p style="margin-top:10px;font-size:14.5px;line-height:1.6">{!! nl2br(e($addr)) !!}</p>

                    <hr class="jsm-hr" style="margin:20px 0">

                    <span class="jsm-label">Hubungi</span>
                    <ul style="list-style:none;padding:0;margin:10px 0 0;display:flex;flex-direction:column;gap:10px;font-size:14.5px">
                        <li><a href="mailto:{{ $mail }}" class="jsm-link">{{ $mail }}</a></li>
                        @if($phone)<li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="jsm-link">{{ $phone }}</a></li>@endif
                        @if($wa)<li><a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="jsm-link">WhatsApp</a></li>@endif
                    </ul>

                    <hr class="jsm-hr" style="margin:20px 0">

                    <span class="jsm-label">Jam operasional</span>
                    <p style="margin-top:8px;font-size:15.5px">{{ $hours }}</p>
                    <p class="jsm-muted" style="font-size:13px;margin-top:6px">Pengamanan di lokasi klien berjalan 24 jam.</p>
                </div>

                <div class="jsm-card jsm-card-pad" style="margin-top:20px">
                    <span class="jsm-label">Melamar pekerjaan?</span>
                    <p class="jsm-muted" style="font-size:14.5px;margin:10px 0 14px">Formulir ini untuk permintaan layanan. Lamaran kerja lewat halaman karier.</p>
                    <a href="{{ route('career') }}" class="jsm-btn jsm-btn-secondary" style="width:100%">Lihat Lowongan</a>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
