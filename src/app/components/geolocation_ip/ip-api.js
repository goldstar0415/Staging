(function () {
	'use strict';
	/**
	 * Directive for locate user by IP
	 */
	angular.module('zoomtivity')
		.factory('ip_api', function ($http, $q) {
			var IP_URL =  "//ip-api.com/json?callback=JSON_CALLBACK";

			return {
				locateUser: function() {
					var deferred = $q.defer();
					$http.jsonp(IP_URL).then(function(d) {
						if (d.data !== undefined && d.data.lat && d.data.lon) {
							deferred.resolve({
								location: {lat: d.data.lat, lng: d.data.lon},
								countryCode: String.prototype.toLowerCase.call(d.data.countryCode||'')
							});
						} else {
							deferred.reject("Couldn't get user location");
						}
					}, function() {
						deferred.reject("Couldn't get user location");
					});
					return deferred.promise;
				}
			};
		});
})();