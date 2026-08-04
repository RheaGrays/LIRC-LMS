const CACHE_NAME = 'lems-kiosk-v1';
const ASSETS_TO_CACHE = [
    '/kiosk',
    '/logo.png',
    '/bg.jpg', // Assuming we have this
    '/beep.mp3' // Assuming we have this
    // We should also cache JS and CSS, but Vite generates hashed filenames. 
    // In a real PWA we'd use workbox or Vite PWA plugin. For this basic offline 
    // fallback, we will cache the main offline page and assets we request.
];

self.addEventListener('install', (event) => {
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

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(event.request).then((response) => {
                // Optionally cache new successful requests dynamically
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                
                // Don't cache dynamic data dynamically if we want fresh
                if(event.request.url.includes('.js') || event.request.url.includes('.css')) {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                
                return response;
            }).catch(() => {
                // If offline and not in cache, fallback to kiosk root if it's a navigation
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
        })
    );
});
