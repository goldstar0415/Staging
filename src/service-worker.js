'use strict';

var CACHE_VERSION = 1;
var CURRENT_CACHES = {
    offline: 'offline-v' + CACHE_VERSION,
    online: 'online-v' + CACHE_VERSION,
};
var OFFLINE_URL = 'assets/offline.html';

function cachePolicy(req) {
    return (
      // Cloudflare CDN files
      ( /cdnjs\.cloudflare\.com.+\.(js|css)$/i.test(req.url) ) ||
      // Google Maps files
      // ( /maps\.googleapis\.com\/maps\/api\/js/i.test(req.url) && req.url.indexOf('AuthenticationService.Authenticate') == -1 ) ||
      // application files
      ( 0 === req.url.indexOf(req.referrer) ) ||
      // facebook SDK
      ( req.url == 'https://connect.facebook.net/en_US/sdk.js' )
    );
}

function createCacheBustedRequest(url) {
    var request = new Request(url, {cache: 'reload'});
    if ('cache' in request) {
        return request;
    }

    var bustedUrl = new URL(url, self.location.href);
    bustedUrl.search += (bustedUrl.search ? '&' : '') + 'cachebust=' + Date.now();
    return new Request(bustedUrl);
}

self.addEventListener('install', function (event) {
    event.waitUntil(fetch(createCacheBustedRequest(OFFLINE_URL)).then(function (response) {
        return caches.open(CURRENT_CACHES.offline).then(function (cache) {
            return cache.put(OFFLINE_URL, response);
        });
    }));
});

self.addEventListener('activate', function (event) {
    var expectedCacheNames = Object.keys(CURRENT_CACHES).map(function (key) {
        return CURRENT_CACHES[key];
    });

    event.waitUntil(caches.keys().then(function (cacheNames) {
        return Promise.all(cacheNames.map(function (cacheName) {
            if (expectedCacheNames.indexOf(cacheName) === -1) {
                console.log('Deleting out of date cache:', cacheName);
                return caches.delete(cacheName);
            }
        }));
    }));
});

self.addEventListener('fetch', function (event) {
  if (event.request.mode === 'navigate' || event.request.method === 'GET' && event.request.headers.get('accept').includes('text/html')) {
    console.log('Handling fetch event for', event.request.url);
    event.respondWith(fetch(createCacheBustedRequest(event.request.url)).catch(function (error) {
      console.log('Fetch failed; returning offline page instead.', error);
      return caches.match(OFFLINE_URL);
    }));
  }
});

self.addEventListener('fetch', function(event) {
    if (event.request.mode === 'navigate' || event.request.method === 'GET' && cachePolicy(event.request)) {
        event.respondWith(
          caches.match(event.request)
            .then(function (response) {

                if (response) {
                    console.debug('>>> Hit: ', event.request.url);
                    return response;
                }

                var fetchRequest = event.request.clone();
                console.debug('>>> Requesting: ' + event.request.url);
                return fetch(fetchRequest).then(
                  function (response) {
                      if (!response || response.status !== 200 || ['basic', 'opaque'].indexOf(response.type) == -1) {
                          console.warn('>>> Do not cache: ' + event.request.url, response);
                          return response;
                      }
                      var responseToCache = response.clone();
                      caches.open(CURRENT_CACHES.online)
                        .then(function (cache) {
                            cache.put(event.request, responseToCache);
                        });

                      return response;
                  }
                );
            })
        );
    }
});
