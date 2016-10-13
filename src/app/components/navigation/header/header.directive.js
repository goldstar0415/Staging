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
    function HeaderController($state, BACKEND_URL, $rootScope, MapService, SignUpService) {
        var vm = this;
        vm.$state = $state;
        vm.BACKEND_URL = BACKEND_URL;
        vm.searchClick = searchClick;
        vm.closeClick = closeClick;
        vm.saveClick = saveClick;
        vm.filterClick = filterClick;
        vm.toggleDropdown = toggleDropdown;
        vm.changeCategory = changeCategory;
        vm.category = $rootScope.sortLayer;
        vm.isSearchOpened = false;
        vm.isDropdownOpened = false;
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

        function closeClick() {
            vm.isDropdownOpened = false;
            MapService.clearSelections();
            MapService.cancelHttpRequest();
            $rootScope.isDrawArea = false;
            angular.element('.leaflet-control-container .map-tools > div').removeClass('active');
        }

        function saveClick() {
            vm.isDropdownOpened = false;
            if ($rootScope.currentUser) {
                MapService.OpenSaveSelectionsPopup();
            } else {
                SignUpService.openModal('SignUpModal.html');
            }
        }

        function filterClick() {
            vm.isDropdownOpened = false;
            console.log('Show filters');
        }

        function toggleDropdown() {
            vm.isDropdownOpened = !vm.isDropdownOpened;
        }

        function changeCategory(category) {
            toggleDropdown();
            if ($rootScope.sortLayer != category) {
                console.log('changed');
                $rootScope.toggleLayer(category);
                vm.category = category;
            }
        }
    }

})();
