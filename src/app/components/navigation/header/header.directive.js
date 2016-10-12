(function() {
    'use strict';

    /*
     * Directive for header
     */

    angular
        .module('zoomtivity')
        .directive('zmHeader', ZoomtivityHeader);

    /** @ngInject */
    function ZoomtivityHeader() {
        return {
            restrict: 'E',
            templateUrl: '/app/components/navigation/header/header.html',
            scope: {
                options: "="
            },
            controller: HeaderController,
            controllerAs: 'Header',
            bindToController: true
        };
    }

    /** @ngInject */
    function HeaderController($state, BACKEND_URL) {
        var vm = this;
        vm.$state = $state;
        vm.BACKEND_URL = BACKEND_URL;
        vm.searchClick = searchClick;
        vm.isSearchOpened = false;
        vm.searchValue = '';

        if (vm.options.snap.disable == "left") {
            vm.toggle = "right";
        } else {
            vm.toggle = "left";
        }

        function searchClick() {
            vm.isSearchOpened = !vm.isSearchOpened;
            vm.searchValue = '';
        }
    }

})();
