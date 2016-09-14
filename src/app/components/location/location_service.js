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
				});
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
				console.log('LocationService', 'update cookie with latest position');
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
		 * @property {number} latitude latitude
		 * @property {number} longitude longitude
		 * @returns {Location}
		 */
		this.getUserLocation = function() {
			var deferred = $q.defer();
			checkPermissions();
			if (permissionsGranted || canAskGeolocation()) {
				if (!permissionsGranted) {
					saveAskGeolocation();
				}
				getUserLocationNavigator().then(function(l) {
					console.log('LocationService', 'coords from navigator');
					deferred.resolve(l);
				});
			} else if ('latitude' in storage && 'longitude' in storage) {
				console.log('LocationService', 'coords from cookie');
				deferred.resolve(storage);
			} else if (ipApiLocation !== null) {
				console.log('LocationService', 'coords from ip-api');
				deferred.resolve(ipApiLocation);
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
				console.log('no lastTimeGetLocation in cookies ');
				return true;
			}
		};
		
		var saveAskGeolocation = function() {
			storage.lastTimeGetLocation = moment().unix();
			saveStorage();
			console.log('saveAskGeolocation');
		};
		
		var saveStorage = function() {
			$cookies.put('browserGeolocation', JSON.stringify(storage), {path: '/', expires: moment().add(getExpireTime(), 'seconds').toDate(), secure: window.location.protocol === "https:"});
		};
		
		var checkPermissions = function() {
			// check if permission is granted
			permissionsGranted = false;
			if ("permissions" in navigator && "query" in navigator.permissions) {
				navigator.permissions.query({name:'geolocation'}).then(function(result) {
					if (result.state === 'granted') {
						permissionsGranted = true;
						watchNavigatorGeolocation();
					} else if (result.state === 'prompt') {
						console.log('navigator', 'geolocation', 'promt');
					}
					// Don't do anything if the permission was denied.
				});
			}
		};
		
		var init = function() {
			// read storage
			try {
				// get last position from cookies if any
				storage = JSON.parse($cookies.get('browserGeolocation'));
				if (storage) {
					console.log('LocationService', 'read storage ok', storage);
				}
			} catch (e) {
				console.log('LocationService', 'read storage failed', e);
			}
			checkPermissions();
			// we always need country code
			getUserLocationIpApi().then(function(l) {
				ipApiLocation = l;
			});
		};
		
		init();
	}
})();
