@extends('layouts.company_profile')

@section('title', 'PT. Jaya Sakti Mandiri Unggul')
@section('description', 'Penyedia tenaga pengamanan bersertifikat, teknologi pengamanan, pelatihan, konsultansi risiko, dan unit K-9 untuk kawasan industri, perbankan, dan fasilitas kesehatan.')
@section('bodyclass', 'jsm-has-hero')

@section('content')
@php
    $lead      = $heroSlides->first();
    // Foto sementara untuk peninjauan. CMS selalu menang; dummy hanya dipakai
    // bila slotnya masih kosong, dan hilang sendiri saat folder dummy dihapus.
    $dummy = function (string $name) {
        return file_exists(public_path("assets/dummy/{$name}.jpg"))
            ? asset("assets/dummy/{$name}.jpg") : null;
    };
    $company   = \App\Models\Setting::get('company_name', 'PT. Jaya Sakti Mandiri Unggul');
    $personnel = \App\Models\Setting::get('stat_personnel');
    $sites     = \App\Models\Setting::get('stat_sites');
    $founded   = \App\Models\Setting::get('founded_year');
    $presence  = \App\Models\Setting::get('stat_attendance');

    $baseLines = [
        ['security-guards', 'Tenaga Pengamanan',      'Anggota Satpam bersertifikat untuk penjagaan, pengawalan, dan pengaturan akses di lokasi klien.'],
        ['technology',      'Teknologi Pengamanan',   'CCTV, kontrol akses, serta sistem absensi dan patroli digital dengan bukti titik dan waktu.'],
        ['training',        'Pelatihan & Pendidikan', 'Penyegaran Gada Pratama, bela diri, penanganan kebakaran, dan tanggap darurat.'],
        ['consultancy',     'Konsultansi & Risiko',   'Kajian kerawanan lokasi, penyusunan prosedur, dan audit penerapan pengamanan.'],
        ['k9',              'Unit K-9',               'Anjing pelacak terlatih untuk deteksi bahan berbahaya dan patroli area luas.'],
    ];

    $sectors = [
        ['Kawasan Industri',    'Pabrik, kawasan berikat, pergudangan', 'sektor-industri'],
        ['Perbankan',           'Kantor cabang, kas keliling, ATM',     'sektor-perbankan'],
        ['Fasilitas Kesehatan', 'Rumah sakit, klinik, laboratorium',    'sektor-kesehatan'],
        ['Perkantoran',         'Gedung bertingkat, ruang sewa',        'sektor-perkantoran'],
        ['Logistik',            'Depo, terminal, pusat distribusi',     'sektor-logistik'],
        ['Ritel',               'Pusat belanja, gerai, minimarket',     'sektor-ritel'],
        ['Pendidikan',          'Kampus, sekolah, asrama',              'sektor-pendidikan'],
        ['Properti Hunian',     'Apartemen, klaster, perumahan',        'sektor-hunian'],
    ];
@endphp

{{-- ===== Hero penuh layar ===== --}}
<section class="jsm-hero">
    @php $heroImg = ($lead && $lead->image) ? asset('storage/' . $lead->image) : $dummy('hero'); @endphp
    @if($heroImg)
        <img class="jsm-hero-img" src="{{ $heroImg }}" alt="" fetchpriority="high">
    @endif
    <div class="jsm-hero-in">
        <div class="jsm-container">
            <span class="jsm-label jsm-label-light">Sejak berdiri, satu tujuan</span>
            <h1 class="jsm-display" style="margin-top:22px">
                {{ $lead->title ?? 'Mitra Anda dalam pengamanan yang terukur' }}
            </h1>
            <p>
                {{ $lead->subtitle ?? $company . ' menempatkan anggota Satpam bersertifikat di kawasan industri, perbankan, dan fasilitas kesehatan — dengan kehadiran dan patroli yang tercatat digital, bukan di buku jaga.' }}
            </p>
            <div class="jsm-hero-actions">
                <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-primary">Minta Penawaran</a>
                <a href="{{ route('services') }}" class="jsm-btn jsm-btn-onnavy">Lihat Layanan</a>
            </div>
            <p class="jsm-hero-note">Setiap penempatan dilaporkan lewat sistem absensi dan patroli milik sendiri.</p>
        </div>
    </div>
</section>

{{-- ===== Angka operasional, latar gelap ===== --}}
<section class="jsm-sec jsm-sec-dark" style="padding-top:72px;padding-bottom:72px">
    <div class="jsm-container">
        <div class="jsm-stats">
            <div class="jsm-stat {{ $personnel ? '' : 'jsm-stat-empty' }}">
                <span class="jsm-stat-num">{{ $personnel ?: '—' }}</span>
                <span class="jsm-stat-lbl">Anggota aktif bersertifikat</span>
            </div>
            <div class="jsm-stat {{ $sites ? '' : 'jsm-stat-empty' }}">
                <span class="jsm-stat-num">{{ $sites ?: '—' }}</span>
                <span class="jsm-stat-lbl">Titik penempatan</span>
            </div>
            <div class="jsm-stat {{ $founded ? '' : 'jsm-stat-empty' }}">
                <span class="jsm-stat-num">{{ $founded ? (date('Y') - (int) $founded) : '—' }}<small>{{ $founded ? ' thn' : '' }}</small></span>
                <span class="jsm-stat-lbl">Tahun beroperasi</span>
            </div>
            <div class="jsm-stat {{ $presence ? '' : 'jsm-stat-empty' }}">
                <span class="jsm-stat-num">{{ $presence ?: '—' }}<small>{{ $presence ? '%' : '' }}</small></span>
                <span class="jsm-stat-lbl">Tingkat kehadiran tercatat</span>
            </div>
        </div>
    </div>
</section>

{{-- ===== Sektor yang dilayani ===== --}}
<section class="jsm-sec">
    <div class="jsm-container">
        <div class="jsm-sechead">
            <span class="jsm-label">Solusi untuk Anda</span>
            <h2 class="jsm-h2">Pengamanan yang disesuaikan dengan risiko tiap sektor</h2>
        </div>
        <div class="jsm-sectors">
            @foreach($sectors as $sector)
            <a class="jsm-sector" href="{{ route('services') }}">
                @if($dummy($sector[2]))
                    <img src="{{ $dummy($sector[2]) }}" alt="" loading="lazy" width="600" height="400">
                @endif
                <span>
                    <span class="jsm-sector-name">{{ $sector[0] }}</span>
                    <span class="jsm-sector-sub">{{ $sector[1] }}</span>
                </span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== Lini layanan ===== --}}
<section class="jsm-sec jsm-sec-sand">
    <div class="jsm-container">
        <div class="jsm-sechead">
            <span class="jsm-label">Lini layanan</span>
            <h2 class="jsm-h2">Lima bidang yang kami kerjakan</h2>
            <span class="jsm-sechead-note"><a href="{{ route('services') }}" class="jsm-link">Rincian tiap layanan &rarr;</a></span>
        </div>
        <div class="jsm-rows">
            @if($services->isNotEmpty())
                @foreach($services as $i => $service)
                <a class="jsm-row" href="{{ route('services') }}#{{ $service->slug }}">
                    <span class="jsm-row-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="jsm-row-title">{{ $service->title }}</span>
                    <span class="jsm-row-desc">{{ Str::limit(strip_tags($service->short_description ?? ''), 120) }}</span>
                    <span class="jsm-row-go">Selengkapnya &rarr;</span>
                </a>
                @endforeach
            @else
                @foreach($baseLines as $i => $line)
                <a class="jsm-row" href="{{ route('services') }}#{{ $line[0] }}">
                    <span class="jsm-row-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="jsm-row-title">{{ $line[1] }}</span>
                    <span class="jsm-row-desc">{{ $line[2] }}</span>
                    <span class="jsm-row-go">Selengkapnya &rarr;</span>
                </a>
                @endforeach
            @endif
        </div>
    </div>
</section>

{{-- ===== Klien ===== --}}
<section class="jsm-sec">
    <div class="jsm-container">
        <div class="jsm-sechead">
            <span class="jsm-label">Dipercaya oleh</span>
            <h2 class="jsm-h2">Klien kami</h2>
            @if($clients->isNotEmpty())
                <span class="jsm-sechead-note"><a href="{{ route('clients') }}" class="jsm-link">Semua klien &rarr;</a></span>
            @endif
        </div>
        @if($clients->isNotEmpty())
            <div class="jsm-logos">
                @foreach($clients as $client)
                <div class="jsm-logo">
                    @if($client->logo)
                        <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}" loading="lazy">
                    @else
                        <span class="jsm-logo-name">{{ $client->name }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div class="jsm-empty">
                <p class="jsm-empty-t">Daftar klien belum dipublikasikan</p>
                <p class="jsm-empty-d">Sebagian klien meminta namanya tidak ditampilkan terbuka. Daftar referensi dapat kami kirimkan atas permintaan.</p>
                <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-secondary">Minta daftar referensi</a>
            </div>
        @endif
    </div>
</section>

{{-- ===== Kegiatan ===== --}}
@if($activities->isNotEmpty())
<section class="jsm-sec jsm-sec-sand">
    <div class="jsm-container">
        <div class="jsm-sechead">
            <span class="jsm-label">Dokumentasi</span>
            <h2 class="jsm-h2">Kegiatan terbaru</h2>
            <span class="jsm-sechead-note"><a href="{{ route('activities') }}" class="jsm-link">Semua kegiatan &rarr;</a></span>
        </div>
        <div class="jsm-grid3">
            @foreach($activities as $activity)
            <article class="jsm-card" style="display:flex;flex-direction:column">
                <div style="background:var(--photo-fallback)">
                    @if($activity->image)
                        <img src="{{ asset('storage/' . $activity->image) }}" alt="{{ $activity->title }}" loading="lazy" width="600" height="360" style="display:block;width:100%;height:210px;object-fit:cover">
                    @else
                        <div class="jsm-imgbox-empty" style="min-height:210px">Tanpa dokumentasi foto</div>
                    @endif
                </div>
                <div class="jsm-card-pad" style="flex:1;display:flex;flex-direction:column">
                    <div style="font-size:13px;font-weight:700;color:var(--gold-ink);letter-spacing:.08em;text-transform:uppercase;margin-bottom:12px">
                        {{ $activity->date ? $activity->date->format('d M Y') : '—' }}@if($activity->type) &middot; {{ $activity->type }}@endif
                    </div>
                    <h3 class="jsm-h3" style="margin-bottom:10px">
                        <a href="{{ route('activities.show', $activity) }}" style="color:inherit;text-decoration:none">{{ $activity->title }}</a>
                    </h3>
                    <p class="jsm-muted" style="font-size:15px;flex:1">{{ Str::limit(strip_tags($activity->short_description ?? ''), 110) }}</p>
                    <a href="{{ route('activities.show', $activity) }}" class="jsm-link" style="margin-top:18px;align-self:flex-start">Baca selengkapnya</a>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== Testimoni ===== --}}
@if($testimonials->isNotEmpty())
<section class="jsm-sec jsm-sec-dark">
    <div class="jsm-container">
        <div class="jsm-sechead">
            <span class="jsm-label jsm-label-light">Penilaian klien</span>
            <h2 class="jsm-h2">Kata mereka yang bekerja langsung dengan anggota kami</h2>
        </div>
        <div class="jsm-grid3">
            @foreach($testimonials->take(3) as $t)
            <blockquote class="jsm-quote">
                <p class="jsm-quote-t">&ldquo;{{ $t->content }}&rdquo;</p>
                <footer class="jsm-quote-by">
                    @if($t->client_photo)
                        <img class="jsm-quote-av" src="{{ asset('storage/' . $t->client_photo) }}" alt="" loading="lazy" width="46" height="46">
                    @endif
                    <div>
                        <div class="jsm-quote-name">{{ $t->client_name }}</div>
                        @if($t->client_position || $t->client_company)
                            <div class="jsm-quote-role">{{ trim(($t->client_position ?? '') . (($t->client_position && $t->client_company) ? ' · ' : '') . ($t->client_company ?? '')) }}</div>
                        @endif
                    </div>
                </footer>
            </blockquote>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===== Ajakan ===== --}}
@php $ctaImg = $dummy('cta'); @endphp
<section class="jsm-cta" @if($ctaImg) style="background-image:url('{{ $ctaImg }}')" @endif>
    <div class="jsm-container jsm-cta-in">
        <div>
            <h2>Butuh tenaga pengamanan untuk lokasi Anda?</h2>
            <p>Sampaikan lokasi, jumlah titik, dan jenis layanan yang dibutuhkan. Kami balas dengan penawaran tertulis.</p>
        </div>
        <div style="display:flex;gap:14px;flex-wrap:wrap">
            <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-primary">Minta Penawaran</a>
            <a href="{{ route('achievements') }}" class="jsm-btn jsm-btn-onnavy">Periksa Legalitas</a>
        </div>
    </div>
</section>
@endsection
