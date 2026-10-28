// Service worker EcoBank: cache cangkang aplikasi saja. api.php TIDAK pernah di-cache.
const CACHE = 'ecobank-v1';
const SHELL = ['./', './index.html', './assets/app.css', './assets/app.js', './manifest.json',
  './assets/icon-192.png', './assets/icon-512.png'];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE).then(c => c.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  const req = e.request;
  const url = new URL(req.url);
  // Hanya tangani GET same-origin; API dan setup selalu langsung ke jaringan
  if (req.method !== 'GET' || url.origin !== location.origin || /\.php$/.test(url.pathname)) return;
  // Network-first: selalu ambil versi terbaru, cache sebagai cadangan saat offline
  e.respondWith(
    fetch(req).then(res => {
      if (res.ok) { const copy = res.clone(); caches.open(CACHE).then(c => c.put(req, copy)); }
      return res;
    }).catch(() => caches.match(req).then(r => r || caches.match('./index.html')))
  );
});
