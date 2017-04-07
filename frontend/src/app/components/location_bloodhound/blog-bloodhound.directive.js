(function () {
    'use strict';
    angular.module('zoomtivity').directive('blogBloodhound', ['API_URL', BlogBloodhound]);

    /** @ngInject */
    function BlogBloodhound(API_URL) {

        return {
            restrict: 'A',
            scope: {
                location: '=',
                address: '=',
                bindMarker: '=',
                limit: '=',
                marker: '=',
                provider: '=',
            },
            controller: controller,
            link: link,
        };

        /**
         * Controller
         */
        function controller($scope, toastr, $rootScope, MapService, $interval, $http, $location) {

            var vm = $scope;
            var provider = vm.provider || 'google';
            var loaded = false;

            (function () {
                var waitForElement = $interval(function () {
                    if (vm.$$input) {
                        init();
                        $interval.cancel(waitForElement);
                    }
                }, 100);
            })();

            vm.$on('typeahead:selected', onTypeaheadSelect);
            vm.$on('typeahead:change', onChange);
            vm.$watch('location', watchLocation);

            vm.setCurrentLocation = setCurrentLocation;

            function init() {
                console.log('init');
                vm.location = vm.location || {lat: '', lng: ''};
                vm.$$input.$element.on('focusin', onFocusin);
                if (vm.address && validateLocation(vm.location)) {
                    display(vm.address);
                    moveOrCreateMarker(vm.location);
                    MapService.GetMap().setView(vm.location, 12);
                }
            }

            function onTypeaheadSelect($event, $model) {
                if (!loaded)
                {
                    loaded = true;
                    switch (vm.provider || provider) {
                        case 'google':
                        {
                            $http.get(API_URL + '/xapi/geocoder/place?placeid=' + $model.place_id, {
                                withCredentials: false
                            }).then(function (response) {
                                if (response.data.status == "OK")
                                {
                                    var data = response.data.result;
                                    vm.location = {lat: data.geometry.location.lat, lng: data.geometry.location.lng}
                                }
                                vm.address = $model.description;
                                var viewAddress = $model.description;
                                display(viewAddress);
                            });
                            break;
                        }
                        case 'spots':
                        default:
                        {
                            vm.location = {lat: $model.lat, lng: $model.lon};
                            vm.address = $model.display_name;
                            var viewAddress = $model.display_name;
                            display(viewAddress);
                        }
                    }
                    var t = setInterval(function () {
                        loaded = false;
                        clearInterval(t);
                        t = undefined;
                    }, 400);
                }
            }

            function onFocusin() {
                if (!validateLocation(vm.location) || vm.$$input.$getModelValue() == '') {
                    MapService.GetMap().on('click', onMapClick);
                }
            }

            function onMapClick(event) {
                if (!validateLocation(vm.location) || vm.$$input.$getModelValue() == '') {
                    vm.location = event.latlng;

                    moveOrCreateMarker(event.latlng);

                    MapService.GetAddressByLatlng(event.latlng, function (data) {
                        vm.address = data.display_name;
                        display(data.display_name);
                    });
                }

                MapService.GetMap().off('click');
            }

            function watchLocation() {
                if (validateLocation(vm.location)) {
                    moveOrCreateMarker(vm.location);
                }
                display(vm.address);
            }

            function setCurrentLocation() {
                if (!$rootScope.currentLocation) {
                    toastr.error('Geolocation error!');
                } else {
                    MapService.GetAddressByLatlng($rootScope.currentLocation, function (data) {
                        vm.location = vm.$$input.$element.latlng;
                        vm.address = data.display_name;
                        display(data.display_name);
                        moveOrCreateMarker(e.latlng);
                    })
                }
            }

            function moveOrCreateMarker(latLng) {
                if (vm.bindMarker) {
                    if (vm.marker) {
                        vm.marker.setLatLng(latLng);
                    } else {
                        createMarker(latLng);
                    }
                    MapService.GetMap().setView(vm.marker.getLatLng());
                }
            }

            function createMarker(latLng) {
                vm.marker = MapService.CreateMarker(latLng, {draggable: true});
                MapService.BindMarkerToInput(vm.marker, function (data) {
                    vm.location = data.latlng;
                    vm.address = data.address;
                    display(data.address);
                });
            }

            function removeMarker() {
                if (vm.marker) {
                    MapService.RemoveMarker(vm.marker);
                    vm.marker = null;
                }
            }

            function display(val) {
                if (vm.$$input) {
                    vm.$$input.$setModelValue(val);
                }
            }

            function validateLocation(location) {
                return location && location.lat && (location.lat + '').trim() != '' && location.lng && (location.lng + '').trim() != '';
            }

            function onChange($event, newValue) {
                if (!newValue || (newValue + '').trim() == '') {
                    removeMarker();
                    vm.location = null;
                    vm.address = '';
                }
            }

        }

        /**
         * Link
         */
        function link(scope, elem, attrs) {

            var limit = scope.limit || 10;

            var URL_CITIES = API_URL + '/xapi/geocoder/autocomplete?q=%QUERY%';/*&types=(cities)*/
            var URL_SPOTS = API_URL + '/map/search?query=%QUERY%&limit=' + limit;

            var bhSource;
            var suggestionTemplate;
            var showPreloader;
            var widgetName; // a random name
            var suggestionsElementCache;


            bindTypeahead();
            scope.$watch('provider', function (value) {
                rebindTypeahead();
            });

            function bindTypeahead()
            {
                var remote = {
                    url: apiResolver(),
                    wildcard: '%QUERY%',
                    prepare: prepareQuery//,
                    /*transform: function(response) {
                        console.log(response);
                        return response;
                    }*/
                };
                    //remote.rateLimitBy = 'throttle';//'debounce';
                    //remote.rateLimitWait = 600;
                bhSource = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: remote
                });

                suggestionTemplate = "<div><span class='title'>%VALUE%</span></div>";
                showPreloader = true;
                widgetName = 'bloodhound-typeahead-' + Math.floor(Math.random() * 1e12);
                suggestionsElementCache = null;
                //if (getProvider() == 'google')
                //{
                    elem.typeahead({
                        minLength: 3
                    }, {
                        name: widgetName,
                        display: 'value',
                        source: bhSource,
                        limit: limit,
                        templates: {
                            suggestion: compileSuggestionTemplate,
                        }
                    })
                /*} else
                {
                    elem.typeahead({
                        minLength: 3,
                    }, {
                        name: widgetName,
                        display: 'value',
                        source: bhSource,
                        limit: 5,
                        templates: {
                            suggestion: function (context) {
                                var template = "<div>" +
                                        "<div class=\"icon {{type}}\"></div>" +
                                        "<div class=\"info\"><div class='title'>{{title}}</div>" +
                                        "<div class=\"address\">{{address}}</div></div></div>";
                                template = template.replace(/\{\{title\}\}/, context.title);
                                template = template.replace(/\{\{type\}\}/, context.type);
                                template = template.replace(/\{\{address\}\}/, context.address);
                                return template;
                            }
                        }
                    })
                }*/
                .bind('typeahead:selected', function (obj, datum) {
                    showPreloader = false; // don't display a preloader when we've selected an item
                    var t = setInterval(function () {
                        showPreloader = true;
                        clearInterval(t);
                        t = undefined;
                    }, 400);
                    scope.$emit('typeahead:selected', datum);
                })
                .on('typeahead:asyncrequest', function () {
                    if (showPreloader) {
                        getSuggestionsElement().addClass('is-loading');
                    }
                })
                .on('typeahead:asynccancel typeahead:asyncreceive', function (event, query, name) {
                    getSuggestionsElement().removeClass('is-loading');
                })
                .on('typeahead:change', function (event, newValue) {
                    scope.$emit('typeahead:change', newValue);
                });
                scope.$$input = {
                    $element: elem,
                    $setModelValue: setModelValue,
                    $getModelValue: getModelValue,
                };
            }

            function rebindTypeahead()
            {
                elem.typeahead('destroy');
                bindTypeahead();
            }

            function getSuggestionsElement() {
                if (!suggestionsElementCache) {
                    suggestionsElementCache = $('.tt-menu:has(.tt-dataset-' + widgetName + ')');
                }
                return suggestionsElementCache;
            }

            function compileSuggestionTemplate(context) {
                return suggestionTemplate.replace(/%VALUE%/, getSuggestionName(context))
            }

            function prepareQuery(query, settings) {
                if (!scope.location) {
                    scope.location = {lat: '', lng: ''};
                }
                settings.url += '&lat=' + scope.location.lat + '&lng=' + scope.location.lng;
                settings.url = settings.url.replace(/%QUERY%/, query);
                return settings;
            }

            function apiResolver() {
                switch (getProvider()) {
                    case 'google':
                    {
                        return URL_CITIES;
                    }
                    case 'spots':
                    default:
                    {
                        return URL_SPOTS;
                    }
                }
            }

            function getSuggestionName(suggestion) {
                switch (getProvider()) {
                    case 'google':
                    {
                        return suggestion.description;
                    }
                    case 'spots':
                    default:
                    {
                        return suggestion.title;
                    }
                }
            }

            function setModelValue(val) {
                elem.typeahead('val', val);
            }

            function getModelValue() {
                var val = elem.typeahead('val');
                return val ? (val + '').trim() : '';
            }

            function getProvider() {
                return scope.provider || 'google';
            }
        }

    }

})();

