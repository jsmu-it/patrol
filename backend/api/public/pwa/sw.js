// Update cache version to force refresh
const CACHE_NAME = 'jsmu-guard-v2';
const urlsToCache = [
    '/pwa/app.css',
    '/pwa/app.js',
    '/images/admin-logo.png',
    '/images/logo.png'
];

self.addEventListener('install', event => {
    // Skip waiting to activate immediately
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('activate', event => {
    // Delete old caches
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            );
        })
    );
    // Take control immediately
    return self.clients.claim();
});

self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Don't cache API requests or HTML pages
    if (url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/app/') ||
        event.request.mode === 'navigate') {
        event.respondWith(fetch(event.request));
        return;
    }

    // Cache static assets only
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );
});
