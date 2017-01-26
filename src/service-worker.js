'use strict';

// this is a gulp inject token
var APP_REVISION = '__GULP_GIT_REVISION__';
var CACHE_VERSION = APP_REVISION == '__GULP_GIT_REV'+'ISION__' ? 'dev' : APP_REVISION;
var CURRENT_CACHES = {
    offline: 'offline-' + CACHE_VERSION,
    online: 'online-' + CACHE_VERSION,
};
var OFFLINE_URL = 'assets/offline.html';

// -- fast config --
var verbose = false; // display console output
var disableCacheInDebugMode = true; // don't cache any files if APP_REVISION == 'dev'
// -----------------

var cachePolicyConfig = {
    cacheableRemote: [
      {match: /cdnjs\.cloudflare\.com/i, opaque: true},
      // {match: /https*:\/\/connect\.facebook\.net\/[^\/]+\/sdk\.js/i, opaque: true}, // fixme: doesn't work (?)
      /fonts\.gstatic\.com/i,
    ],
    cacheableLocal: [
      /^\/bower_components\/.+/i,
      /^\/assets\/.+/i,
      /^\/app\/.+/i,
      /^\/scripts\/.+/i,
      /^\/fonts\/.+/i,
      // '/',
      '/favicon.ico',
    ],
};

function cachePolicy(req) {
    var localOrigin = getLocalOrigin();
    var isLocalRequest = localOrigin && -1 !== req.url.toLowerCase().indexOf(localOrigin);
    var rules = isLocalRequest ? cachePolicyConfig.cacheableLocal : cachePolicyConfig.cacheableRemote;
    var result = {
        opaque: false,
        allow: false,
    };
    rules.forEach(function(rule){
        if (!result.allow) {
            rule = parsePolicyRule(rule); // todo: cache the parsed rule
            var src = isLocalRequest ? req.url.substr(localOrigin.length - 1) : req.url;
            if (verbose) console.log('>>> Src: ', src);
            if ((rule.type == 'string' && -1 !== src.toLowerCase().indexOf(rule.match)) || (rule.type == 'regexp' && rule.match.test(src))) {
                result.allow = true;
                result.opaque = rule.opaque;
            }

        }
    });
    return result;
}

function getLocalOrigin() {
    return self.registration && self.registration.scope ? self.registration.scope.toLowerCase() : null;
}

function parsePolicyRule(rule) {
    var type = 'string', match, opaque = false;
    if (typeof rule === 'string') {
        match = rule.toLowerCase();
    } else if (typeof rule === 'object') {
        if (rule.test) {
            type = 'regexp';
            match = rule;
        } else {
            type = typeof rule.match === 'string' ? 'string' : 'regexp';
            match = type == 'string' ? rule.match.toLowerCase() : rule.match;
            opaque = rule.opaque;
        }
    }
    return {type: type, match: match, opaque: opaque};
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

// todo: check it out
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

    if (CACHE_VERSION == 'dev' && disableCacheInDebugMode) {
        return;
    }

    if (event.request.mode === 'navigate' || event.request.method === 'GET' && cachePolicy(event.request)) {
        event.respondWith(
          caches.match(event.request)
            .then(function (response) {
                if (response) {
                    if (verbose) console.debug('>>> Hit: ', event.request.url);
                    return response;
                }
                if (verbose) console.debug('>>> Miss: ' + event.request.url);
                var req = event.request.clone();
                var policyResult = cachePolicy(req);
                if (verbose) console.log('* Policy Result: ', event.request.url, policyResult);
                var fetchRequest;
                if (policyResult.opaque) {
                    fetchRequest = new Request(req.url, {
                        mode: 'cors',
                        referrer: req.referrer,
                        referrerPolicy: "no-referrer-when-downgrade",
                        credentials: 'omit',
                    });
                } else {
                    fetchRequest = req;
                }

                if (verbose) console.debug('>> fetchRequest', fetchRequest);

                return fetch(fetchRequest).then(
                  function (response) {
                      if (response && response.status === 200 && policyResult.allow && ( (policyResult.opaque && response.type == 'cors') || (!policyResult.opaque && response.type == 'basic'))) {
                          var responseToCache = response.clone();
                          caches.open(CURRENT_CACHES.online)
                            .then(function (cache) {
                                cache.put(event.request, responseToCache);
                            });
                          return response;
                      } else {
                          if (verbose) console.warn('>>> Do not cache: ' + event.request.url, response, policyResult, event.request);
                          return response;
                      }
                  }
                );
            })
        );
    }
});
