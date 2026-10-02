// Amiga Travel - High Performance Static Asset Service Worker
const CACHE_NAME = 'amiga-static-v2';

// Core static assets preloaded on service worker installation
const PRECACHE_ASSETS = [
    // Background images for all pages
    '/images/amiga-backgrounds/bg-1.jpg',
    '/images/amiga-backgrounds/bg-2.jpg',
    '/images/amiga-backgrounds/bg-3.jpg',
    '/images/amiga-backgrounds/bg-4.jpg',
    '/images/amiga-backgrounds/bg-5.jpg',
    '/images/amiga-backgrounds/bg-6.jpg',
    '/images/amiga-backgrounds/bg-7.jpg',
    '/images/amiga-backgrounds/bg-8.jpg',

    // Brand logos and favicons
    '/images/amiga_logo_white_outline.png',
    '/images/amiga-logo-transparent.png',
    '/images/amiga-logo.jpg',
    '/images/app-icon-original.png',
    '/favicon.ico',
    '/manifest.json',

    // Partner logos
    '/images/2GO-Logo.png',
    '/images/AirAsia-Logo.png',
    '/images/CebuPecific-Logo.png',
    '/images/Pal-Logo.jfif',
    '/images/starlite-Logo.jfif',
    '/images/Starlite_Logo.png',
    '/images/world-map.svg',

    // Store badge icons
    '/images/badges/appgallery-icon.png',
    '/images/badges/apple-icon.png',
    '/images/badges/appstore-icon.png',

    // Hero video
    '/video/animation1.mp4',
];

// Install: Cache critical static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            // Pre-cache assets gracefully (individual failure doesn't abort entire install)
            await Promise.allSettled(
                PRECACHE_ASSETS.map((url) =>
                    fetch(url, { cache: 'no-cache' })
                        .then((res) => {
                            if (res.ok) return cache.put(url, res);
                        })
                        .catch(() => {})
                )
            );
            return self.skipWaiting();
        })
    );
});

// Activate: Claim clients and clean old cache versions
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            )
        ).then(() => self.clients.claim())
    );
});

// Fetch: Cache-First strategy for static assets (images, videos, fonts, build scripts, styles)
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Only intercept GET requests
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Bypass dynamic routes, livewire, admin, api, and search requests
    if (
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/livewire') ||
        url.pathname.startsWith('/api') ||
        url.pathname.includes('/book/status') ||
        request.headers.get('X-Livewire')
    ) {
        return;
    }

    // Determine if request is a static asset
    const isStaticAsset =
        url.pathname.startsWith('/images/') ||
        url.pathname.startsWith('/video/') ||
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/vendor/') ||
        /\.(png|jpg|jpeg|jfif|gif|svg|webp|ico|mp4|webm|woff2?|ttf|eot|css|js)$/i.test(url.pathname);

    if (isStaticAsset) {
        event.respondWith(
            caches.open(CACHE_NAME).then(async (cache) => {
                // 1. Check Cache first
                const cachedResponse = await cache.match(request);
                if (cachedResponse) {
                    return cachedResponse;
                }

                // 2. Fall back to Network and populate cache
                try {
                    const networkResponse = await fetch(request);
                    if (networkResponse && (networkResponse.status === 200 || networkResponse.type === 'opaque')) {
                        // Store clone in cache for instant subsequent hits
                        cache.put(request, networkResponse.clone());
                    }
                    return networkResponse;
                } catch (err) {
                    // Return cached response if available
                    if (cachedResponse) return cachedResponse;
                    throw err;
                }
            })
        );
    }
});
