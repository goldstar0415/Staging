(function () {
	'use strict';
	/**
	 * Directive for locate user by IP
	 */
	angular.module('zoomtivity')
		.factory('ip_api', function ($http, $q) {
			var IP_URL =  "//freegeoip.net/json?callback=JSON_CALLBACK";

			return {
				locateUser: function() {
					var deferred = $q.defer();
					$http.jsonp(IP_URL).then(function(d) {
						if (d.data !== undefined && d.data.latitude && d.data.longitude) {
							deferred.resolve({
								location: {lat: d.data.latitude, lng: d.data.longitude},
								countryCode: String.prototype.toLowerCase.call(d.data.country_code||'')
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