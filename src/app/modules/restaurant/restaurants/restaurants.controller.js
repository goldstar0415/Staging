(function () {
    'use strict';

    angular
        .module('zoomtivity')
        .controller('RestaurantsController', RestaurantsController);

    /** @ngInject */
    function RestaurantsController($scope, Restaurant, ScrollService, API_URL) {
        var vm = this;
        var isLoadedRestaurants = false;

        vm.API_URL = API_URL;
        vm.restaurants = {};

        var params = {
          page: 0,
          limit: 30
        };
        vm.pagination = new ScrollService(Restaurant.query, vm.restaurants, params);
        
    }
})();