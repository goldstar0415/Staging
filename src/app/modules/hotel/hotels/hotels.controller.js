(function () {
    'use strict';

    angular
        .module('zoomtivity')
        .controller('HotelsController', HotelsController);

    /** @ngInject */
    function HotelsController($scope, Hotel, ScrollService, API_URL) {
        var vm = this;
        var isLoadedHotels = false;

        vm.API_URL = API_URL;
        vm.hotels = {};

        var params = {
          page: 0,
          limit: 30
        };
        vm.pagination = new ScrollService(Hotel.query, vm.hotels, params);
        
    }
})();