(function() {
    'use strict';

    angular
        .module('zoomtivity')
        .directive('weather', Weather);

    /** @ngInject */
    function Weather() {
        return {
            restrict: 'E',
            templateUrl: '/app/components/weather/weather.html',
            scope: {
                lat: '=',
                lng: '='
            },
            controller: WeatherController,
            controllerAs: 'Weather',
            bindToController: true
        };

        /** @ngInject */
        function WeatherController($scope, $rootScope, $http) {
            var vm = this;

            var elem = ".new-weather-container";
            $scope.$watch(function() { return angular.element(elem).is(':visible') }, function() {
                vm.init();
            })

            vm.init = function() {
                var skycons = new Skycons({"color": "#0b2639"});
                skycons.add("weather-main-icon", Skycons.PARTLY_CLOUDY_DAY);

                document.querySelectorAll('.weather-hourly .icon canvas').forEach(function(el) {
                    skycons.add(el, Skycons.PARTLY_CLOUDY_DAY);
                });

                skycons.play();
            }
        }
    }
})();
