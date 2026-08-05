const CACHE_NAME = 'lems-kiosk-v10';
const ASSETS_TO_CACHE = [
    '/kiosk',
    '/cjc-logo.jpeg',
    '/bg.jpg',
    '/discussion_room.jpg',
    '/quiet_zone.jpg'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        }).catch(err => console.log('SW install cache error', err))
    );
});

self.addEventListener('fetch', (event) => {
    // Only intercept Kiosk and its assets, not admin paths
    if (event.request.url.includes('/admin')) return;
    
    // API requests (like /kiosk/log) should NOT be cached. Let them fail so 
    // our offline-queue.js can catch it and store it in localStorage.
    if (event.request.method === 'POST') return;
    if (event.request.url.includes('/kiosk/lookup') || 
        event.request.url.includes('/kiosk/last') || 
        event.request.url.includes('/kiosk/occupancy')) return;

    // BYPASS CACHE FOR DEVELOPMENT: Always fetch from network first.
    event.respondWith(
        fetch(event.request).then((response) => {
            if (!response || response.status !== 200 || response.type !== 'basic') {
                return response;
            }
            
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
                cache.put(event.request, responseToCache);
            });
            
            return response;
        }).catch(() => {
            // If offline, fallback to cache
            return caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                if (event.request.mode === 'navigate') {
                    return caches.match('/kiosk');
                }
            });
        })
    );
});

self.addEventListener('activate', (event) => {
    const cacheAllowlist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheAllowlist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});
