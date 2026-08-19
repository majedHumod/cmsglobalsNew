const CACHE_VERSION = 'cmsglobals-v7-client-only';
const OFFLINE_URL = '/offline';
const PRECACHE_URLS = ['/client/home', OFFLINE_URL, '/manifest.json'];

function shouldBypassCache(request) {
  const url = new URL(request.url);

  // Never interfere with APIs or non-client marketing/admin navigations.
  if (url.pathname.startsWith('/api/')) {
    return true;
  }

  if (request.mode === 'navigate' && !url.pathname.startsWith('/client')) {
    return true;
  }

  if (url.pathname.startsWith('/client/') && url.pathname !== '/client/home') {
    return true;
  }

  return false;
}

function isCacheableResponse(response) {
  return response.ok && response.type === 'basic';
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_VERSION).then((cache) => cache.addAll(PRECACHE_URLS)).catch(() => Promise.resolve())
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET' || shouldBypassCache(event.request)) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        if (isCacheableResponse(response)) {
          const cloned = response.clone();
          caches.open(CACHE_VERSION).then((cache) => cache.put(event.request, cloned)).catch(() => {});
        }
        return response;
      })
      .catch(async () => {
        const cached = await caches.match(event.request);
        if (cached) {
          return cached;
        }
        if (event.request.mode === 'navigate') {
          return caches.match(OFFLINE_URL);
        }
        return new Response('Offline', { status: 503, statusText: 'Offline' });
      })
  );
});

self.addEventListener('push', (event) => {
  let payload = { title: 'تذكير جديد', body: '', url: '/client/notifications' };
  try {
    if (event.data) {
      payload = { ...payload, ...event.data.json() };
    }
  } catch (error) {
    payload.body = event.data ? event.data.text() : '';
  }

  event.waitUntil(
    self.registration.showNotification(payload.title, {
      body: payload.body,
      icon: '/manifest.json',
      data: { url: payload.url || '/client/notifications' },
    })
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const targetUrl = event.notification.data?.url || '/client/notifications';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if ('focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
      return undefined;
    })
  );
});
