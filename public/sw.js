const CACHE_NAME = 'halis-v1';
const STATIC_CACHE = 'halis-static-v1';
const DYNAMIC_CACHE = 'halis-dynamic-v1';

const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/images/pwa/icon-192.png',
    '/images/pwa/icon-512.png',
];

const CACHE_STRATEGIES = {
    static: ["/images/", "/build/", "/vendor/"],
    networkFirst: ["/api/", "/tenant/", "/platform/"],
    staleWhileRevalidate: ["/css/", "/js/"],
};

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(() => {});
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
                    .map(key => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET') return;
    if (url.pathname.startsWith('/filament/')) return;
    if (url.pathname.includes('/livewire/')) return;

    if (url.pathname === '/' || url.pathname === '/register' || url.pathname === '/login') {
        event.respondWith(networkFirst(request));
        return;
    }

    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    if (isNavigationRequest(request)) {
        event.respondWith(networkFirst(request));
        return;
    }

    event.respondWith(staleWhileRevalidate(request));
});

function cacheFirst(request) {
    return caches.match(request).then(cached => {
        if (cached) return cached;
        return fetch(request).then(response => {
            if (response.ok) {
                const clone = response.clone();
                caches.open(STATIC_CACHE).then(cache => cache.put(request, clone));
            }
            return response;
        }).catch(() => new Response('Offline', { status: 503 }));
    });
}

function networkFirst(request) {
    return fetch(request).then(response => {
        if (response.ok) {
            const clone = response.clone();
            caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
        }
        return response;
    }).catch(() => {
        return caches.match(request).then(cached => {
            return cached || new Response(offlinePage(), {
                headers: { 'Content-Type': 'text/html' }
            });
        });
    });
}

function staleWhileRevalidate(request) {
    return caches.match(request).then(cached => {
        const fetchPromise = fetch(request).then(response => {
            if (response.ok) {
                const clone = response.clone();
                caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
            }
            return response;
        }).catch(() => cached);

        return cached || fetchPromise;
    });
}

function isStaticAsset(pathname) {
    return pathname.startsWith('/images/') ||
           pathname.startsWith('/build/') ||
           pathname.startsWith('/vendor/') ||
           pathname.endsWith('.css') ||
           pathname.endsWith('.js') ||
           pathname.endsWith('.png') ||
           pathname.endsWith('.jpg') ||
           pathname.endsWith('.svg') ||
           pathname.endsWith('.ico') ||
           pathname.endsWith('.woff2') ||
           pathname.endsWith('.woff');
}

function isNavigationRequest(request) {
    return request.mode === 'navigate' ||
           (request.method === 'GET' && request.headers.get('accept')?.includes('text/html'));
}

function offlinePage() {
    return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - HALIS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1a1a2e; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { text-align: center; max-width: 400px; padding: 40px; }
        .icon { font-size: 4rem; margin-bottom: 16px; }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; }
        p { color: #64748b; font-size: .95rem; line-height: 1.6; margin-bottom: 24px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; background: #6366f1; color: #fff; border: none; border-radius: 8px; font-size: .9rem; font-weight: 600; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #4f46e5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">📡</div>
        <h1>You're Offline</h1>
        <p>It looks like you've lost your internet connection. Some features may be unavailable until you're back online.</p>
        <button class="btn" onclick="window.location.reload()">Try Again</button>
    </div>
</body>
</html>`;
}

self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(clientList => {
            for (const client of clientList) {
                if (client.url.includes(self.registration.scope) && 'focus' in client) {
                    return client.focus();
                }
            }
            return clients.openWindow('/');
        })
    );
});

self.addEventListener('push', event => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'HALIS';
    const body = data.body || 'You have a new notification';
    const icon = '/images/pwa/icon-192.png';
    const badge = '/images/pwa/icon-96.png';

    event.waitUntil(
        self.registration.showNotification(title, { body, icon, badge, data: data.url ? { url: data.url } : {} })
    );
});
