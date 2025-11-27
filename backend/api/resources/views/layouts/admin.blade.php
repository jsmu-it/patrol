<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CKEditor CDN -->
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 text-gray-900">
<div class="min-h-screen flex">
    <aside class="w-64 bg-slate-900 text-white flex flex-col">
        <div class="px-6 py-4 border-b border-slate-700 font-semibold text-lg">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2">
                <img src="{{ asset('images/admin-logo.png') }}" alt="JSMUGuard" class="h-8 w-auto">
                <span>JSMUGuard Admin</span>
            </a>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-2 text-sm">
            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.dashboard')) bg-slate-800 @endif">Dashboard</a>
            <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.users.*')) bg-slate-800 @endif">Karyawan</a>
            <a href="{{ route('admin.projects.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.projects.*')) bg-slate-800 @endif">Project</a>
            <a href="{{ route('admin.patrol.checkpoints.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.patrol.checkpoints.*')) bg-slate-800 @endif">Patroli</a>
            
            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Laporan</div>
            <a href="{{ route('admin.reports.attendance') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.reports.attendance')) bg-slate-800 @endif">Laporan Absensi</a>
            <a href="{{ route('admin.reports.patrol') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.reports.patrol')) bg-slate-800 @endif">Laporan Patroli</a>
            
            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Persetujuan</div>
            <a href="{{ route('admin.approvals.attendance') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.approvals.attendance')) bg-slate-800 @endif">Absensi Dinas</a>
            <a href="{{ route('admin.approvals.leave') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.approvals.leave')) bg-slate-800 @endif">Izin / Cuti</a>

            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Company Profile</div>
            <a href="{{ route('admin.cms-hero-slides.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.cms-hero-slides.*')) bg-slate-800 @endif">Hero Slider</a>
            <a href="{{ route('admin.cms-contents.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.cms-contents.*')) bg-slate-800 @endif">Konten Halaman</a>
            <a href="{{ route('admin.cms-services.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.cms-services.*')) bg-slate-800 @endif">Layanan</a>
            <a href="{{ route('admin.cms-achievements.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.cms-achievements.*')) bg-slate-800 @endif">Penghargaan</a>
            <a href="{{ route('admin.cms-activities.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.cms-activities.*')) bg-slate-800 @endif">Aktivitas</a>
            <a href="{{ route('admin.cms-clients.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.cms-clients.*')) bg-slate-800 @endif">Klien</a>
            <a href="{{ route('admin.cms-careers.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.cms-careers.*')) bg-slate-800 @endif">Karir</a>
            <a href="{{ route('admin.cms-contacts.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.cms-contacts.*')) bg-slate-800 @endif">Pesan Masuk</a>

            <div class="pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Keuangan</div>
            <a href="{{ route('admin.payroll.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.payroll.*')) bg-slate-800 @endif">Payroll</a>
            <a href="{{ route('admin.pkwt.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('admin.pkwt.*')) bg-slate-800 @endif">PKWT</a>
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}" class="px-4 pb-4 mt-auto">
            @csrf
            <button type="submit" class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 rounded text-sm font-medium">Logout</button>
        </form>
    </aside>

    <main class="flex-1 flex flex-col">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">@yield('page_title', 'Dashboard')</h1>
                <p class="text-xs text-gray-500">Sistem Absensi &amp; Patroli Satpam</p>
            </div>
            <div class="text-sm text-gray-600">
                @auth
                    <div class="font-medium">{{ auth()->user()->name }}</div>
                    <div class="text-xs">{{ auth()->user()->username }} &mdash; {{ auth()->user()->role }}</div>
                @endauth
            </div>
        </header>

        <section class="p-6">
            @if (session('status'))
                <div class="mb-4 px-4 py-2 rounded bg-emerald-100 text-emerald-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 px-4 py-2 rounded bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc pl-4">
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
