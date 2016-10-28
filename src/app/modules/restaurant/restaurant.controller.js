(function () {
    'use strict';

    angular
        .module('zoomtivity')
        .controller('RestaurantController', RestaurantController);

    /** @ngInject */
    function RestaurantController(restaurant) {
        var vm         = this;
        vm             = _.extend(vm, restaurant);
    }
})();