<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0C6CF2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="JSMU Guard">
    <link rel="manifest" href="/pwa/manifest.json">
    <link rel="apple-touch-icon" href="/images/admin-logo.png">
    <link rel="icon" href="/images/logo.png">
    <title>@yield('title', 'JSMU Guard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/pwa/app.css">
    @stack('styles')
</head>
<body>
    <div id="app">
        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    @auth
    @else
    @if(!request()->is('app') && !request()->is('app/'))
    <nav class="bottom-nav">
        <a href="/app/home" class="{{ request()->is('app/home') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            <span>Home</span>
        </a>
        <a href="/app/history" class="{{ request()->is('app/history') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
            <span>Riwayat</span>
        </a>
        <a href="/app/profile" class="{{ request()->is('app/profile') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            <span>Profil</span>
        </a>
    </nav>
    @endif
    @endauth

    <script src="/pwa/app.js"></script>
    @stack('scripts')

    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/pwa/sw.js')
                .then(reg => console.log('SW registered'))
                .catch(err => console.log('SW failed', err));
        }
    </script>
</body>
</html>
