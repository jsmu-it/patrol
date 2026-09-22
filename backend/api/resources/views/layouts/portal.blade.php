<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal Kerja') — PT. Jaya Sakti Mandiri Unggul</title>
    <link rel="icon" href="{{ asset('images/admin-logo.png') }}">
    <link href="{{ asset('assets/css/tailwind.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/fonts/inter-local.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/portal.css') }}" rel="stylesheet">
    <script defer src="{{ asset('assets/js/alpine.min.js') }}"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
    @stack('styles')
</head>
<body class="pt min-h-screen flex flex-col">

@auth
<header class="pt-header sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            <a href="{{ route('portal.home') }}" class="pt-brand pt-fokus">
                <img src="{{ asset('images/admin-logo.png') }}" alt="JSMU">
                <span class="pt-brand-teks">
                    Portal Kerja
                    <span class="pt-brand-sub">Jaya Sakti Mandiri Unggul</span>
                </span>
            </a>

            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('portal.home') }}" class="pt-navlink pt-fokus {{ request()->routeIs('portal.home') ? 'is-aktif' : '' }}">Beranda</a>
                <a href="{{ route('portal.storage.index') }}" class="pt-navlink pt-fokus {{ request()->routeIs('portal.storage.*') ? 'is-aktif' : '' }}">Penyimpanan Data</a>
                <a href="{{ route('portal.joc.index') }}" class="pt-navlink pt-fokus {{ request()->routeIs('portal.joc.*') ? 'is-aktif' : '' }}">JOC</a>
                <a href="{{ route('portal.rptk.index') }}" class="pt-navlink pt-fokus {{ request()->routeIs('portal.rptk.*') ? 'is-aktif' : '' }}">RPTK</a>
            </nav>

            <div class="flex items-center gap-2">
            @php $belumDibaca = auth()->user()->unreadNotifications()->count(); @endphp
            <a href="{{ route('portal.notifikasi.index') }}" class="relative p-2 rounded-full hover:bg-gray-100 pt-fokus" aria-label="Pemberitahuan">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1"/></svg>
                @if($belumDibaca > 0)
                    <span class="absolute top-1 right-1 min-w-4 h-4 px-1 rounded-full bg-red-600 text-white text-xs font-semibold flex items-center justify-center">{{ $belumDibaca > 9 ? '9+' : $belumDibaca }}</span>
                @endif
            </a>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2.5 pt-fokus rounded-full" aria-label="Menu akun">
                    <span class="text-right hidden sm:block leading-tight">
                        <span class="block text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</span>
                        <span class="block text-xs text-gray-500">{{ auth()->user()->username }}</span>
                    </span>
                    <span class="pt-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </button>
                <div x-show="open" x-cloak @click.away="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 transform -translate-y-1"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="absolute right-0 mt-2 w-52 pt-card py-1 z-50">
                    <div class="px-4 py-2 border-b border-gray-100 sm:hidden">
                        <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->username }}</div>
                    </div>
                    <a href="{{ route('portal.storage.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 md:hidden">Penyimpanan Data</a>
                    <a href="{{ route('portal.storage.sampah') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Tempat Sampah</a>
                    <form action="{{ route('portal.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Keluar</button>
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>
</header>
@endauth

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('status'))
            <div x-data="{ tampil: true }" x-show="tampil" x-cloak
                 class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm flex items-start justify-between gap-3">
                <span>{{ session('status') }}</span>
                <button @click="tampil = false" class="text-green-700 hover:text-green-900" aria-label="Tutup">&times;</button>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif
        @yield('content')
    </div>
</main>

<footer class="border-t border-gray-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-gray-500 flex flex-wrap gap-2 justify-between">
        <span>&copy; {{ date('Y') }} PT. Jaya Sakti Mandiri Unggul</span>
        <a href="{{ route('home') }}" class="hover:text-gray-900">Situs utama</a>
    </div>
</footer>

@stack('scripts')
</body>
</html>
