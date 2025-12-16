<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-scrollbar::-webkit-scrollbar { width: 4px; }
        .sidebar-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 2px; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 text-gray-900" x-data="{ sidebarOpen: false }" x-cloak>

<!-- Mobile Sidebar Overlay -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
     @click="sidebarOpen = false">
</div>

<div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white flex flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0">
        
        <!-- Logo -->
        <div class="px-4 py-4 border-b border-slate-700 font-semibold text-lg flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2">
                <img src="{{ asset('images/admin-logo.png') }}" alt="JSMUGuard" class="h-8 w-auto">
                <span class="hidden sm:inline">JSMUGuard</span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded hover:bg-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto sidebar-scrollbar">
            @if(auth()->check() && auth()->user()->role === 'SUPERADMIN')
            <div class="pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Manajemen User</div>
            <a href="{{ route('admin.admin-users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.admin-users.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Manajemen User
            </a>
            @endif
            
            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.dashboard')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.users.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Karyawan
            </a>
            <a href="{{ route('admin.projects.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.projects.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Project
            </a>
            <a href="{{ route('admin.patrol.checkpoints.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.patrol.checkpoints.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Patroli
            </a>
            
            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Laporan</div>
            <a href="{{ route('admin.reports.attendance') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.reports.attendance')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Laporan Absensi
            </a>
            <a href="{{ route('admin.reports.patrol') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.reports.patrol')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan Patroli
            </a>
            
            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Persetujuan</div>
            <a href="{{ route('admin.approvals.attendance') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.approvals.attendance')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Absensi Dinas
            </a>
            <a href="{{ route('admin.approvals.leave') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.approvals.leave')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Izin / Cuti
            </a>

            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">HRD</div>
            <a href="{{ route('admin.hrd.applications') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.hrd.applications*') && !request()->routeIs('admin.hrd.rejected')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Pelamar
            </a>
            <a href="{{ route('admin.hrd.rejected') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.hrd.rejected')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                Ditolak
            </a>
            <a href="{{ route('admin.hrd.cv.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.hrd.cv.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CV Karyawan
            </a>
            <a href="{{ route('admin.payroll.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.payroll.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Payroll
            </a>
            <a href="{{ route('admin.pkwt.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.pkwt.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PKWT
            </a>

            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Company Profile</div>
            <a href="{{ route('admin.cms-hero-slides.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.cms-hero-slides.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Hero Slider
            </a>
            <a href="{{ route('admin.cms-contents.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.cms-contents.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                Profil Perusahaan
            </a>
            <a href="{{ route('admin.cms-services.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.cms-services.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Layanan
            </a>
            <a href="{{ route('admin.cms-achievements.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.cms-achievements.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                Penghargaan
            </a>
            <a href="{{ route('admin.cms-activities.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.cms-activities.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Aktivitas
            </a>
            <a href="{{ route('admin.cms-clients.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.cms-clients.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Klien
            </a>
            <a href="{{ route('admin.cms-careers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.cms-careers.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Karir
            </a>
            <a href="{{ route('admin.cms-applications.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.cms-applications.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Aplikasi
            </a>
            <a href="{{ route('admin.cms-contacts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition @if(request()->routeIs('admin.cms-contacts.*')) bg-slate-800 @endif">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Pesan Masuk
            </a>
        </nav>
        
        <!-- Logout -->
        <form method="POST" action="{{ route('admin.logout') }}" class="px-3 pb-4 mt-auto">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </button>
        </form>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 lg:ml-0">
        <!-- Top Header -->
        <header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-lg sm:text-xl font-semibold text-gray-800 truncate">@yield('page_title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-500 hidden sm:block">Sistem Absensi & Patroli Satpam</p>
                </div>
            </div>
            <div class="text-sm text-gray-600 text-right">
                @auth
                    <div class="font-medium truncate max-w-[120px] sm:max-w-none">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-400 hidden sm:block">{{ auth()->user()->role }}</div>
                @endauth
            </div>
        </header>

        <!-- Page Content -->
        <section class="p-4 sm:p-6 flex-1 overflow-x-auto">
            @if (session('status'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </section>
    </main>
</div>

@stack('scripts')
</body>
</html>
