<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PT. Jaya Sakti Mandiri Unggul')</title>
    <meta name="description" content="@yield('description', 'Penyedia tenaga pengamanan bersertifikat, teknologi pengamanan, pelatihan, konsultansi risiko, dan unit K-9.')">

    <link href="{{ asset('assets/css/tailwind.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/fonts/plex-local.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/jsmu.css') }}" rel="stylesheet">
    <script defer src="{{ asset('assets/js/alpine.min.js') }}"></script>
    @stack('styles')
</head>
<body class="jsm @yield('bodyclass', 'jsm-no-hero')">

@php
    $siteLogo        = \App\Models\Setting::get('logo');
    $companyName     = \App\Models\Setting::get('company_name', 'PT. Jaya Sakti Mandiri Unggul');
    $licenseNo       = \App\Models\Setting::get('license_number');
    $foundedYear     = \App\Models\Setting::get('founded_year');
    $footerAddress   = \App\Models\Setting::get('footer_address', 'Jakarta, Indonesia');
    $footerEmail     = \App\Models\Setting::get('footer_email', 'info@jsmuguard.com');
    $footerPhone     = \App\Models\Setting::get('footer_phone', '');
    $footerCopyright = \App\Models\Setting::get('footer_copyright', '&copy; ' . date('Y') . ' ' . $companyName . '. Seluruh hak dilindungi.');
    $socialFacebook  = \App\Models\Setting::get('social_facebook');
    $socialInstagram = \App\Models\Setting::get('social_instagram');
    $socialTwitter   = \App\Models\Setting::get('social_twitter');
    $socialLinkedin  = \App\Models\Setting::get('social_linkedin');
    $socialYoutube   = \App\Models\Setting::get('social_youtube');
    $socialWhatsapp  = \App\Models\Setting::get('social_whatsapp');

    $navMenus = [
        ['id'=>'profil','label'=>'Profil','route'=>route('profile'),'active'=>request()->routeIs('profile'),'items'=>[
            ['#about','Tentang Kami','Sejarah dan posisi perusahaan'],
            ['#visi-misi','Visi &amp; Misi',null],
            ['#hsse','HSSE','Keselamatan, keamanan, lingkungan'],
            ['#archipelago','Cakupan Nusantara','Wilayah operasi'],
        ]],
        ['id'=>'layanan','label'=>'Layanan','route'=>route('services'),'active'=>request()->routeIs('services'),'items'=>[
            ['#security-guards','Tenaga Pengamanan',null],
            ['#technology','Teknologi Pengamanan',null],
            ['#training','Pelatihan &amp; Pendidikan',null],
            ['#consultancy','Konsultansi &amp; Risiko',null],
            ['#k9','Unit K-9',null],
        ]],
        ['id'=>'kegiatan','label'=>'Kegiatan','route'=>route('activities'),'active'=>request()->routeIs('activities*'),'items'=>[
            [route('activities'),'Semua Kegiatan',null],
            [route('activities').'?type=internal','Kegiatan Internal',null],
        ]],
    ];
@endphp

<a href="#konten" class="jsm-skip">Lewati ke konten</a>

<header class="jsm-header" :class="scrolled ? 'is-scrolled' : (hero ? 'is-top' : '')"
        x-data="{ mobile:false, scrolled:false, hero:document.body.classList.contains('jsm-has-hero') }"
        x-init="scrolled = window.scrollY > 40"
        @scroll.window="scrolled = window.scrollY > 40"
        @keydown.escape.window="mobile=false">
    <div class="jsm-header-in">
        <a href="{{ route('home') }}" class="jsm-brand" aria-label="{{ $companyName }} — beranda">
            @if($siteLogo)
                <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $companyName }}">
            @else
                <span class="jsm-brand-mark" aria-hidden="true">JS</span>
                <span class="jsm-brand-name">Jaya Sakti Mandiri Unggul</span>
            @endif
        </a>

        <nav class="jsm-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="jsm-navlink" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>

            @foreach($navMenus as $menu)
            <div class="jsm-menu"
                 x-data="{ open:false }"
                 @keydown.escape="if(open){ open=false; $refs.trigger.focus() }"
                 @focusout="if(!$el.contains($event.relatedTarget)) open=false"
                 @mouseleave="open=false">
                <button type="button"
                        x-ref="trigger"
                        class="jsm-navlink"
                        @if($menu['active']) aria-current="page" @endif
                        :aria-expanded="open ? 'true' : 'false'"
                        aria-controls="dd-{{ $menu['id'] }}"
                        @click="open = !open"
                        @mouseenter="open = true">
                    {{ $menu['label'] }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="jsm-dropdown" id="dd-{{ $menu['id'] }}" x-show="open" x-cloak x-transition.opacity.duration.120ms>
                    <a href="{{ $menu['route'] }}">Semua {{ $menu['label'] }}</a>
                    @foreach($menu['items'] as $item)
                        <a href="{{ Str::startsWith($item[0], '#') ? $menu['route'].$item[0] : $item[0] }}">
                            {!! $item[1] !!}@if($item[2])<small>{{ $item[2] }}</small>@endif
                        </a>
                    @endforeach
                </div>
            </div>
            @endforeach

            <a href="{{ route('achievements') }}" class="jsm-navlink" @if(request()->routeIs('achievements')) aria-current="page" @endif>Legalitas</a>
            <a href="{{ route('clients') }}" class="jsm-navlink" @if(request()->routeIs('clients')) aria-current="page" @endif>Klien</a>
            <a href="{{ route('faq') }}" class="jsm-navlink" @if(request()->routeIs('faq')) aria-current="page" @endif>FAQ</a>
            <a href="{{ route('career') }}" class="jsm-navlink" @if(request()->routeIs('career*')) aria-current="page" @endif>Karier</a>
            <a href="{{ route('contact') }}" class="jsm-header-cta">Minta Penawaran</a>
        </nav>

        <button type="button" class="jsm-burger" @click="mobile = true" :aria-expanded="mobile ? 'true' : 'false'" aria-controls="menu-ponsel" aria-label="Buka menu navigasi">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <div class="jsm-mobile" id="menu-ponsel" x-show="mobile" x-cloak role="dialog" aria-modal="true" aria-label="Menu navigasi">
        <div class="jsm-mobile-top">
            <span class="jsm-brand-name">Jaya Sakti Mandiri Unggul</span>
            <button type="button" class="jsm-burger" @click="mobile = false" aria-label="Tutup menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="jsm-mobile-body">
            <a href="{{ route('home') }}" class="jsm-mobile-item">Beranda</a>
            @foreach($navMenus as $menu)
            <div x-data="{ sub:false }">
                <button type="button" class="jsm-mobile-item" @click="sub = !sub" :aria-expanded="sub ? 'true' : 'false'" aria-controls="m-{{ $menu['id'] }}">
                    {{ $menu['label'] }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="jsm-mobile-sub" id="m-{{ $menu['id'] }}" x-show="sub" x-cloak>
                    <a href="{{ $menu['route'] }}">Semua {{ $menu['label'] }}</a>
                    @foreach($menu['items'] as $item)
                        <a href="{{ Str::startsWith($item[0], '#') ? $menu['route'].$item[0] : $item[0] }}">{!! $item[1] !!}</a>
                    @endforeach
                </div>
            </div>
            @endforeach
            <a href="{{ route('achievements') }}" class="jsm-mobile-item">Legalitas</a>
            <a href="{{ route('clients') }}" class="jsm-mobile-item">Klien</a>
            <a href="{{ route('faq') }}" class="jsm-mobile-item">FAQ</a>
            <a href="{{ route('career') }}" class="jsm-mobile-item">Karier</a>
            <div style="margin-top:24px">
                <a href="{{ route('contact') }}" class="jsm-btn jsm-btn-primary" style="width:100%">Minta Penawaran</a>
            </div>
        </div>
    </div>
</header>

<main id="konten">
    @yield('content')
</main>

<footer class="jsm-footer">
    <div class="jsm-container">
        <div class="jsm-footer-grid">
            <div>
                @if($siteLogo)
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $companyName }}" style="height:44px;margin-bottom:14px">
                @else
                    <div class="jsm-brand-name" style="font-size:19px">{{ $companyName }}</div>
                @endif
                <p style="margin-top:12px;line-height:1.65;max-width:38ch">
                    Tenaga pengamanan bersertifikat, didukung sistem absensi dan patroli digital.
                </p>
                @if($licenseNo)
                    <p class="jsm-mono" style="margin-top:14px;font-size:13px;color:var(--gold-300)">Izin operasional {{ $licenseNo }}</p>
                @endif
            </div>

            <div>
                <h4>Perusahaan</h4>
                <ul>
                    <li><a href="{{ route('profile') }}">Profil</a></li>
                    <li><a href="{{ route('services') }}">Layanan</a></li>
                    <li><a href="{{ route('achievements') }}">Legalitas</a></li>
                    <li><a href="{{ route('activities') }}">Kegiatan</a></li>
                    <li><a href="{{ route('clients') }}">Klien</a></li>
                    <li><a href="{{ route('testimonials') }}">Testimoni</a></li>
                    <li><a href="{{ route('career') }}">Karier</a></li>
                </ul>
            </div>

            <div>
                <h4>Kontak</h4>
                <ul>
                    <li>{!! nl2br(e($footerAddress)) !!}</li>
                    <li><a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a></li>
                    @if($footerPhone)<li><a href="tel:{{ preg_replace('/\s+/', '', $footerPhone) }}">{{ $footerPhone }}</a></li>@endif
                    @if($socialWhatsapp)<li><a href="https://wa.me/{{ $socialWhatsapp }}" target="_blank" rel="noopener">WhatsApp</a></li>@endif
                </ul>
            </div>

            <div>
                <h4>Akses Internal</h4>
                <ul>
                    <li><a href="{{ route('admin.login') }}">Absensi &amp; Patroli</a></li>
                    <li><a href="{{ route('application') }}">Unduh Aplikasi</a></li>
                    <li><a href="{{ route('privacy') }}">Kebijakan Privasi</a></li>
                </ul>

                @if($socialFacebook || $socialInstagram || $socialTwitter || $socialLinkedin || $socialYoutube)
                <h4 style="margin-top:26px">Ikuti Kami</h4>
                <div class="jsm-social">
                    @if($socialFacebook)<a href="{{ $socialFacebook }}" target="_blank" rel="noopener" aria-label="Facebook"><svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>@endif
                    @if($socialInstagram)<a href="{{ $socialInstagram }}" target="_blank" rel="noopener" aria-label="Instagram"><svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.639.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.315 1.347 20.646.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.584.012 4.849.07 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.265.07 1.646.07 4.85s-.015 3.585-.07 4.85c-.053 1.17-.25 1.805-.413 2.227-.217.562-.477.96-.896 1.381-.419.419-.82.679-1.381.896-.422.164-1.057.36-2.227.413-1.265.06-1.646.07-4.85.07-3.204 0-3.584-.012-4.849-.07-1.17-.055-1.805-.249-2.227-.413-.562-.217-.959-.477-1.381-.896-.42-.42-.68-.819-.896-1.381-.164-.422-.36-1.057-.413-2.227-.06-1.265-.07-1.646-.07-4.85 0-3.204.012-3.584.07-4.85.054-1.17.248-1.805.413-2.227.217-.562.476-.96.896-1.381.419-.419.819-.68 1.381-.896.422-.164 1.057-.36 2.227-.415 1.265-.057 1.646-.07 4.849-.07zM12 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zm0 2.16a4.002 4.002 0 110 8.004 4.002 4.002 0 010-8.004zm5.338-3.205a1.44 1.44 0 110 2.88 1.44 1.44 0 010-2.88z"/></svg></a>@endif
                    @if($socialLinkedin)<a href="{{ $socialLinkedin }}" target="_blank" rel="noopener" aria-label="LinkedIn"><svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a>@endif
                    @if($socialTwitter)<a href="{{ $socialTwitter }}" target="_blank" rel="noopener" aria-label="Twitter/X"><svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>@endif
                    @if($socialYoutube)<a href="{{ $socialYoutube }}" target="_blank" rel="noopener" aria-label="YouTube"><svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>@endif
                </div>
                @endif
            </div>
        </div>

        <div class="jsm-footer-bottom">
            <div>{!! $footerCopyright !!}</div>
            <div><a href="{{ route('privacy') }}">Kebijakan Privasi</a></div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
