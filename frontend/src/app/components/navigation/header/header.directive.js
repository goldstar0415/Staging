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
            bindToController: true,
            link: function (scope, elem, attrs) {
                elem.bind("keydown keypress", function (event) {
                    if (event.key === 'Escape') {
                        scope.clearInput();
                        event.preventDefault();
                    }
                });
            }
        };
    }

    /** @ngInject */
    function HeaderController($state, $scope, BACKEND_URL, $rootScope, MapService, SignUpService, GoogleMapsPlacesService) {
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

        vm.back = function() {
            MapService.closeAll();
        }

        $scope.$watch(function() {
            return angular.element('.spots-nav').is(':visible')
        }, function() {
            vm.category = $rootScope.sortLayer;
        });

        $scope.clearInput = function () {
            vm.searchValue = '';   
        };

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
        }

        function toggleDropdown() {
            vm.isDropdownOpened = !vm.isDropdownOpened;
        }

        function changeCategory(category) {
            toggleDropdown();
            if ($rootScope.sortLayer != category) {
                $rootScope.toggleLayer(category);
                vm.category = category;
            }
        }

        $scope.$on('typeahead:selected', function (event, data) {
            if (data.type === 'location') {
                GoogleMapsPlacesService.getDetails({placeId: data.place_id}).then(function (data) {
                    console.log(data.geometry.viewport);

                    var params = {spotLocation: {
                        lat: data.geometry.location.lat(),
                        lng: data.geometry.location.lng()
                    }};
                    if ($rootScope.$state.current.name !== 'index') {
                        $state.go('index', params);
                    }
                    if (data.geometry.viewport === undefined ) {
                        MapService.FocusMapToGivenLocation(params.spotLocation);
                    } else {
                        MapService.FitBoundsByCoordinates([
                            [
                                data.geometry.viewport.getSouthWest().lat(),
                                data.geometry.viewport.getSouthWest().lng()
                            ],
                            [
                                data.geometry.viewport.getNorthEast().lat(),
                                data.geometry.viewport.getNorthEast().lng()
                            ]
                        ]);
                    }
                });
            } else if (data.type === 'spot') {
                $state.go('spot', {spot_id: data.spotId});
            }
        });
    }

})();
