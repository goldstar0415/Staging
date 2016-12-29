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
    function BloodhoundRemoteSearch(API_URL) {
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
                        url: API_URL + '/search/spots?query=%QUERY',
                        wildcard: '%QUERY',
                        prepare: function(query, settings) {
                            settings.url += '&lat='+scope.location.lat+'&lng='+scope.location.lng;
                            settings.url = settings.url.replace(/\%QUERY/, query);
                            return settings;
                        }
                    }
                });

                elem.typeahead(null, {
                    name: 'best-pictures',
                    display: 'value',
                    source: bestPictures,
                    limit: Infinity,
                    templates: {
                        suggestion: function(context) {
                            var template = "<div><span class='title'>{{value}}</span>{{dist}}</span> {{group}}</div>";
                            template = template.replace(/\{\{value\}\}/, context.value);

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
                      $('.tt-menu').addClass('is-loading');
                  })
                  .on('typeahead:asynccancel typeahead:asyncreceive', function() {
                      $('.tt-menu').removeClass('is-loading');
                  });
                elem.bind('typeahead:selected', function(obj, datum, name) {
                    scope.$emit('typeahead:selected', datum);
                });
            }
        };
    }
})();