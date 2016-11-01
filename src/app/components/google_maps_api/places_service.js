(function () {
    'use strict';
    angular.module('zoomtivity')
        .service('GoogleMapsPlacesService', GoogleMapsPlacesService);

    /** @ngInject */
    function GoogleMapsPlacesService($document, $q) {

        this.getDetails = function (request) {
            var deferred = $q.defer();
            googleService.getDetails(request, function (place, status) {
                if (status === 'OK') {
                    deferred.resolve(place);
                } else {
                    deferred.reject(status);
                }
            });
            return deferred.promise;
        };

        var googleService = null;

        var init = function() {
            $document.find('body').eq(0).append(angular.element("<div id='google-map-subst'></div>"));
            googleService = new google.maps.places.PlacesService(document.getElementById('google-map-subst'));
        };

        init();
    }
})();
