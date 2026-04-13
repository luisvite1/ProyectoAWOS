const CACHE_NAME = 'los-litros-v1';
const ASSETS = [
    '/ProyectoAWOS/index.html',
    '/ProyectoAWOS/comandero.html',
    '/ProyectoAWOS/frontend/css/global.css',
    '/ProyectoAWOS/frontend/css/comandero.css'
];

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
    );
});

self.addEventListener('fetch', e => {
    e.respondWith(
        caches.match(e.request).then(res => res || fetch(e.request))
    );
});