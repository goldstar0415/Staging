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
    function HeaderController($state, $scope, BACKEND_URL, $rootScope, MapService, SignUpService, $http, $q, GOOGLE_API_KEY, GoogleMapsPlacesService) {
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
        var inRequest = false;
        function searchChanged() {
            inRequest = true;
            return $http.get(BACKEND_URL + '/search/spots', {params: {query: vm.searchValue}}).then(function (d) {
                console.log(d);
                var suggestions = [];
                suggestions.push({formatted_suggestion: 'suggestions: '});
                if (!d.data) {
                    return [];
                }
                // anything you want can go here and will safely be run on the next digest.
                if (Array.isArray(d.data.suggestions)) {
                    console.log(d.data.suggestions);
                    d.data.suggestions.forEach(function (e) {
                        suggestions.push({
                            formatted_suggestion: e
                        });
                    });
                }
                suggestions.push({formatted_suggestion: ' '});
                suggestions.push({formatted_suggestion: 'spots: '});
                if (Array.isArray(d.data.spots)) {
                    console.log(d.data.spots);
                    d.data.spots.forEach(function (e) {
                        suggestions.push({
                            formatted_suggestion: e.title
                        });
                    });
                }
                return suggestions;
            }).finally(function () {
                inRequest = false;
            });
        }

        this.selectSuggestion = function($item, $model, $label, $event) {
            console.log('selectSuggestion', $item);
            return $item;
        };

        $scope.$on('typeahead:selected', function (event, data) {
            if (data.type === 'location') {
                GoogleMapsPlacesService.getDetails({placeId: data.place_id}).then(function (data) {
                    console.log(data.geometry.viewport);

                    var params = {spotLocation: {
                        lat: data.geometry.location.lat(),
                        lng: data.geometry.location.lng()
                    }};
                    if ($rootScope.$state.current.name == 'index') {
                    } else {
                        console.log('state params', params);
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
