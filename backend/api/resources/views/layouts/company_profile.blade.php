<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'JSMU Guard - Security Services')</title>
    <meta name="description" content="@yield('description', 'Professional Security Services')">
    
    <!-- Tailwind CSS (Local) -->
    <link href="{{ asset('assets/css/tailwind.min.css') }}" rel="stylesheet">
    <!-- Inter Font (Local) -->
    <link href="{{ asset('assets/fonts/inter-local.css') }}" rel="stylesheet">
    <!-- Alpine.js (Local) -->
    <script defer src="{{ asset('assets/js/alpine.min.js') }}"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: linear-gradient(to right, rgba(0,0,0,0.7), rgba(0,0,0,0.3)); }
    </style>
</head>
<body class="antialiased text-gray-800 bg-white flex flex-col min-h-screen">

    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false, profileOpen: false, servicesOpen: false, activityOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                @php
                    $headerLogo = \App\Models\Setting::get('logo');
                @endphp
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center">
                            @if($headerLogo)
                                <img src="{{ asset('storage/' . $headerLogo) }}" alt="JSMU Guard" class="h-12">
                            @else
                                <span class="text-2xl font-bold text-blue-900 tracking-wider">JSMU GUARD</span>
                            @endif
                        </a>
                    </div>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex md:items-center md:space-x-6">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-900 font-medium px-3 py-2 rounded-md text-sm uppercase">Home</a>
                    
                    <!-- Profile Dropdown -->
                    <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="text-gray-700 group-hover:text-blue-900 font-medium px-3 py-2 rounded-md text-sm uppercase flex items-center" @click="open = !open">
                            Profile
                            <svg class="ml-1 h-4 w-4 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-50" style="display: none;">
                            <a href="{{ route('profile') }}#about" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">About Us</a>
                            <a href="{{ route('profile') }}#visi-misi" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Visi Misi</a>
                            <a href="{{ route('profile') }}#hsse" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">HSSE</a>
                            <a href="{{ route('profile') }}#archipelago" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Archipelago</a>
                        </div>
                    </div>

                    <!-- Services Dropdown -->
                    <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <a href="{{ route('services') }}" class="text-gray-700 group-hover:text-blue-900 font-medium px-3 py-2 rounded-md text-sm uppercase flex items-center" @click="open = !open">
                            Our Services
                            <svg class="ml-1 h-4 w-4 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </a>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-50" style="display: none;">
                            <a href="{{ route('services') }}#security-guards" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Security Guards</a>
                            <a href="{{ route('services') }}#technology" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Technology Application</a>
                            <a href="{{ route('services') }}#training" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Training & Education</a>
                            <a href="{{ route('services') }}#consultancy" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Consultancy & Risk</a>
                            <a href="{{ route('services') }}#k9" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">K-9 Guard</a>
                        </div>
                    </div>

                    <a href="{{ route('achievements') }}" class="text-gray-700 hover:text-blue-900 font-medium px-3 py-2 rounded-md text-sm uppercase">Achievement</a>
                    
                    <!-- Activity Dropdown -->
                    <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <a href="{{ route('activities') }}" class="text-gray-700 group-hover:text-blue-900 font-medium px-3 py-2 rounded-md text-sm uppercase flex items-center" @click="open = !open">
                            Activity
                            <svg class="ml-1 h-4 w-4 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </a>
                         <div x-show="open" 
                              x-transition:enter="transition ease-out duration-100"
                              x-transition:enter-start="transform opacity-0 scale-95"
                              x-transition:enter-end="transform opacity-100 scale-100"
                              x-transition:leave="transition ease-in duration-75"
                              x-transition:leave-start="transform opacity-100 scale-100"
                              x-transition:leave-end="transform opacity-0 scale-95"
                              class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-50" style="display: none;">
                            <a href="{{ route('activities') }}?type=internal" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Internal Event</a>
                            <a href="{{ route('activities') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">All Activities</a>
                        </div>
                    </div>

                    <a href="{{ route('clients') }}" class="text-gray-700 hover:text-blue-900 font-medium px-3 py-2 rounded-md text-sm uppercase">Our Client</a>
                    <a href="{{ route('faq') }}" class="text-gray-700 hover:text-blue-900 font-medium px-3 py-2 rounded-md text-sm uppercase">FAQ</a>
                    <a href="{{ route('career') }}" class="text-gray-700 hover:text-blue-900 font-medium px-3 py-2 rounded-md text-sm uppercase">Career</a>
                    
                    <a href="{{ route('contact') }}" class="bg-blue-900 text-white hover:bg-blue-800 px-4 py-2 rounded-md text-sm font-bold uppercase transition duration-300 shadow-lg">Contact Us</a>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" class="md:hidden bg-white border-t border-gray-200" x-transition>
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">HOME</a>
                
                <div class="space-y-1">
                    <button @click="profileOpen = !profileOpen" class="w-full text-left flex justify-between px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
                        PROFILE
                        <svg class="h-5 w-5" :class="{'rotate-180': profileOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="profileOpen" class="pl-4 space-y-1">
                        <a href="{{ route('profile') }}#about" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-50">About Us</a>
                        <a href="{{ route('profile') }}#visi-misi" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-50">Visi Misi</a>
                    </div>
                </div>

                <a href="{{ route('services') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">SERVICES</a>
                <a href="{{ route('achievements') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">ACHIEVEMENT</a>
                <a href="{{ route('activities') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">ACTIVITY</a>
                <a href="{{ route('clients') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">OUR CLIENT</a>
                <a href="{{ route('faq') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">FAQ</a>
                <a href="{{ route('career') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">CAREER</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-md text-base font-medium text-blue-900 font-bold hover:bg-gray-50">CONTACT US</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    @php
        $footerAddress = \App\Models\Setting::get('footer_address', 'Jakarta, Indonesia');
        $footerEmail = \App\Models\Setting::get('footer_email', 'info@jsmuguard.com');
        $footerPhone = \App\Models\Setting::get('footer_phone', '');
        $footerCopyright = \App\Models\Setting::get('footer_copyright', '&copy; ' . date('Y') . ' JSMU Guard. All rights reserved.');
        $siteLogo = \App\Models\Setting::get('logo');
        $socialFacebook = \App\Models\Setting::get('social_facebook');
        $socialInstagram = \App\Models\Setting::get('social_instagram');
        $socialTwitter = \App\Models\Setting::get('social_twitter');
        $socialLinkedin = \App\Models\Setting::get('social_linkedin');
        $socialYoutube = \App\Models\Setting::get('social_youtube');
        $socialWhatsapp = \App\Models\Setting::get('social_whatsapp');
    @endphp
    <footer class="bg-gray-900 text-white pt-12 pb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    @if($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="JSMU Guard" class="h-12 mb-4">
                    @else
                        <h3 class="text-xl font-bold mb-4 tracking-wider">JSMU GUARD</h3>
                    @endif
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Providing professional security services with integrity and modern technology solutions.
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="{{ route('profile') }}" class="hover:text-white transition">About Us</a></li>
                        <li><a href="{{ route('services') }}" class="hover:text-white transition">Our Services</a></li>
                        <li><a href="{{ route('career') }}" class="hover:text-white transition">Career</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-start">
                            <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>{!! nl2br(e($footerAddress)) !!}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <a href="mailto:{{ $footerEmail }}" class="hover:text-white">{{ $footerEmail }}</a>
                        </li>
                        @if($footerPhone)
                        <li class="flex items-start">
                            <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <a href="tel:{{ $footerPhone }}" class="hover:text-white">{{ $footerPhone }}</a>
                        </li>
                        @endif
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Follow Us</h4>
                    <div class="flex flex-wrap gap-3">
                        @if($socialFacebook)
                        <a href="{{ $socialFacebook }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition" title="Facebook">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        @endif
                        @if($socialInstagram)
                        <a href="{{ $socialInstagram }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition" title="Instagram">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.639.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.315 1.347 20.646.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.584.012 4.849.07 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.265.07 1.646.07 4.85s-.015 3.585-.07 4.85c-.053 1.17-.25 1.805-.413 2.227-.217.562-.477.96-.896 1.381-.419.419-.82.679-1.381.896-.422.164-1.057.36-2.227.413-1.265.06-1.646.07-4.85.07-3.204 0-3.584-.012-4.849-.07-1.17-.055-1.805-.249-2.227-.413-.562-.217-.959-.477-1.381-.896-.42-.42-.68-.819-.896-1.381-.164-.422-.36-1.057-.413-2.227-.06-1.265-.07-1.646-.07-4.85 0-3.204.012-3.584.07-4.85.054-1.17.248-1.805.413-2.227.217-.562.476-.96.896-1.381.419-.419.819-.68 1.381-.896.422-.164 1.057-.36 2.227-.415 1.265-.057 1.646-.07 4.849-.07zM12 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zm0 2.16a4.002 4.002 0 110 8.004 4.002 4.002 0 010-8.004zm5.338-3.205a1.44 1.44 0 110 2.88 1.44 1.44 0 010-2.88z"/></svg>
                        </a>
                        @endif
                        @if($socialTwitter)
                        <a href="{{ $socialTwitter }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition" title="Twitter/X">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        @endif
                        @if($socialLinkedin)
                        <a href="{{ $socialLinkedin }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition" title="LinkedIn">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        @endif
                        @if($socialYoutube)
                        <a href="{{ $socialYoutube }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition" title="YouTube">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        @endif
                        @if($socialWhatsapp)
                        <a href="https://wa.me/{{ $socialWhatsapp }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition" title="WhatsApp">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-sm text-center text-gray-500">
                <div class="mb-2">
                    <a href="{{ route('privacy') }}" class="hover:text-white transition">Privacy Policy</a>
                </div>
                {!! $footerCopyright !!}
            </div>
        </div>
    </footer>

</body>
</html>
