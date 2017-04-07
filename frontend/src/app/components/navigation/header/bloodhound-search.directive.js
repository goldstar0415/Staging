(function() {
    'use strict';
    /*
     * Directive for header
     */
    console.log('BloodhoundRemoteSearch');
    angular
        .module('zoomtivity')
        .directive('bloodhoundRemote', BloodhoundRemoteSearch);

    /** @ngInject */
    function BloodhoundRemoteSearch(MapService, API_URL, SpotService, $location, $rootScope) {
        return {
            restrict: 'A',
            scope: {
                options: "=bloodhoundRemoteOptions"
            },
            controller: function (LocationService, $scope) {
                $scope.location = {
                    lat: '', lng: ''
                };
                LocationService.getUserLocation().then(function (l) {
                    $scope.location.lat =  l.latitude;
                    $scope.location.lng =  l.longitude;
                });
            },
            link: function (scope, elem, attrs) {
                console.log('Init search', scope.options);
                
                var bestPictures = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    // prefetch: '../data/films/post_1960.json',
                    remote: {
                        url: API_URL + '/map/search?query=%QUERY',
                        wildcard: '%QUERY',
                        rateLimitBy: 'debounce',
                        rateLimitWait: 600,
                        prepare: function(query, settings) {
                            settings.url += '&lat='+scope.location.lat+'&lng='+scope.location.lng;
                            settings.url = settings.url.replace(/\%QUERY/, query);
                            return settings;   
                        }
                    }
                });
                var widgetName = 'bloodhound-typeahead-' + Math.floor(Math.random()*1e12); // a random name
                var suggestionsElementCache = null;
                
                elem.typeahead({
                    minLength: 4,
                }, {
                    name: widgetName,
                    display: 'value',
                    source: bestPictures,
                    limit: Infinity,
                    templates: {
                        suggestion: function(context) {
                            var template = "<div><div class=\"icon {{type}}\"></div><div class=\"info\"><div class='title'>{{title}}</div><div class=\"address\">{{address}}</div></div><span>{{dist}}</span> {{group}}</div>";
                            template = template.replace(/\{\{title\}\}/, context.title);
                            template = template.replace(/\{\{type\}\}/, context.type);
                            template = template.replace(/\{\{address\}\}/, context.address);
                            var group = "";
                            var dist  = "";
                            switch(context.type) {
                                case 'location':
                                    group = "<span class='group-title'>Locations <span class='ion-ios-location'></span></span>";
                                    dist  = "";
                                    break;
                                case 'spot':
                                    group = "<span class='group-title'>Spots <span class='ion-ios-information'></span></span>";
                                    dist  = "<span class='dist' style='color: lightgray;'>&nbsp;" + Math.round(context.dist / 1000) + " km</span>"
                                    break;
                            }
                            if (!context.first) {
                                group = "";
                            }
                            template = template.replace(/\{\{group\}\}/, group);
                            template = template.replace(/\{\{dist\}\}/, dist);
                            return template;
                        }
                    }
                })
                  .on('typeahead:asyncrequest', function() {
                      getSuggestionsElement().addClass('is-loading');
                  })
                  .on('typeahead:asynccancel typeahead:asyncreceive', function() {
                      getSuggestionsElement().removeClass('is-loading');
                  })
                  .bind('typeahead:selected', function(obj, datum, name) {
                    scope.$emit('typeahead:selected', datum);
                    $location.path((datum.user_id  ? datum.user_id : 0) + '/spot/' + datum.spot_id + '/');
                    MapService.ChangeState('big', true);
                    $rootScope.$apply();
                  });

                function getSuggestionsElement() {
                    if (!suggestionsElementCache) {
                        suggestionsElementCache = $('.tt-menu:has(.tt-dataset-'+widgetName+')');
                    }
                    return suggestionsElementCache;
                }
            }
        };
    }
})();
