(function () {
	'use strict';
	angular.module('zoomtivity')
		.service('LocationService', LocationService);

	/** @ngInject */
	function LocationService(ip_api, $cookies, $q) {
		var watchId							= null;
		var permissionsGranted				= false;
		var ipApiLocation					= null;
		var storage							= {};
		var COOKIE_DESKTOP_EXPIRE_SEC		= 60*60*24*7; // 7 days
		var COOKIE_MOBILE_EXPIRE_SEC		= 60*60*24*1; // 1 day
		var TIME_NEXT_LOCATION_PROMT_SEC	= 60*60*24*1; // 1 day
		var timeLastUpdateCookie			= 0;
		var TIME_USE_COOKIE_SEC				= 60;		  // 1 min
		
		var getUserLocationIpApi = function() {
			var deferred = $q.defer();
			ip_api.locateUser().then(function(l) {
				deferred.resolve({latitude: l.location.lat, longitude: l.location.lng, countryCode: l.countryCode});
			}, function(e) {
				deferred.reject(e);
			});
			return deferred.promise;
		};
		
		var getUserLocationNavigator = function() {
			var deferred = $q.defer();
			if ("geolocation" in navigator) {
				navigator.geolocation.getCurrentPosition(function(position) {
					var location = {latitude: position.coords.latitude, longitude: position.coords.longitude};
					console.log(position);
					if (ipApiLocation) {
						location.countryCode = ipApiLocation.countryCode;
					}
					checkPermissions();
					storeLocation(location);
					deferred.resolve(location);
				}, function(e) {
					deferred.reject(e);
				}, {timeout: 5000});
			} else {
				deferred.reject('Geolocation not supported');
			}
			return deferred.promise;
		};
		
		var watchNavigatorGeolocation = function() {
			if (watchId !== null) {
				return;
			}
			watchId = navigator.geolocation.watchPosition(function(position) {
				timeLastUpdateCookie = moment().unix();
				storeLocation(position.coords);
			});
		};
		/**
		 * @param {Location} position
		 * @returns {undefined}
		 */
		var storeLocation = function(location) {
			if ('latitude' in location && 'longitude' in location) {
				storage.latitude	= location.latitude;
				storage.longitude	= location.longitude;
				saveStorage();
			}
		};
		
		/**
		 * @type {Object} Location
		 * @param {boolean} force Force get location
		 * @property {number} latitude latitude
		 * @property {number} longitude longitude
		 * @returns {Location}
		 */
		this.getUserLocation = function(force) {
			var deferred = $q.defer();
			checkPermissions();
			
			var useStorage		= ('latitude' in storage && 'longitude' in storage) ? storage : null;
			// get location from storage or from ip-api
			var storageOrApi	= useStorage || ipApiLocation;
			
			if ((moment().unix() - timeLastUpdateCookie < TIME_USE_COOKIE_SEC) && useStorage) {
				// get fast from fresh cookie data
				// sorry for code duplicating
				deferred.resolve(storage);
			} else {
				// try to get from navigator
				if (permissionsGranted || canAskGeolocation() || force) {
					if (!permissionsGranted) {
						saveAskGeolocation();
					}
					getUserLocationNavigator().then(function(l) {
						deferred.resolve(l);
					}).
					catch(function() {
						storageOrApi ? deferred.resolve(storageOrApi) : deferred.reject("Couldn't locate user");
					});
				} else {
					storageOrApi ? deferred.resolve(storageOrApi) : deferred.reject("Couldn't locate user");
				}
			}
			return deferred.promise;
		};
		
		var getExpireTime = function () {
			return L.Browser.touch ? COOKIE_MOBILE_EXPIRE_SEC: COOKIE_DESKTOP_EXPIRE_SEC;
		};
		
		var canAskGeolocation = function() {
			if ("lastTimeGetLocation" in storage) {
				return (moment().unix() - storage.lastTimeGetLocation) >= TIME_NEXT_LOCATION_PROMT_SEC ? true : false;
			} else {
				return true;
			}
		};
		
		var saveAskGeolocation = function() {
			storage.lastTimeGetLocation = moment().unix();
			saveStorage();
		};
		
		var saveStorage = function() {
			$cookies.put('browserGeolocation', JSON.stringify(storage), {path: '/', expires: moment().add(getExpireTime(), 'seconds').toDate(), secure: window.location.protocol === "https:"});
		};
		
		var checkPermissions = function() {
			// check if permission is granted
			if ("permissions" in navigator && "query" in navigator.permissions) {
				navigator.permissions.query({name:'geolocation'}).then(function(result) {
					if (result.state === 'granted') {
						permissionsGranted = true;
						watchNavigatorGeolocation();
					} else if (result.state === 'prompt') {
						permissionsGranted = false;
					}
					// Don't do anything if the permission was denied.
				});
			} else {
				permissionsGranted = false;
			}
		};
		
		var init = function() {
			// read storage
			try {
				// get last position from cookies if any
				storage = JSON.parse($cookies.get('browserGeolocation'));
				if (storage) {
				} else {
					storage = {};
				}
			} catch (e) {
				console.log('LocationService', 'read storage failed', e);
				storage = {};
			}
			checkPermissions();
			// we always need country code
			getUserLocationIpApi().then(function(l) {
				ipApiLocation = l;
				storage.countryCode = l.countryCode;
			});
		};
		
		init();
	}
})();
