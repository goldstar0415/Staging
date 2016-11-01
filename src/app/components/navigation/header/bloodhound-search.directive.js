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
    function BloodhoundRemoteSearch(BACKEND_URL) {
        return {
            restrict: 'A',
            scope: {
                options: "=bloodhoundRemoteOptions"
            },
            link: function (scope, elem, attrs) {
                console.log('Init search', scope.options);
                var bestPictures = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    prefetch: '../data/films/post_1960.json',
                    remote: {
                        url: BACKEND_URL+'/search/spots?query=%QUERY',
                        wildcard: '%QUERY'
                    }
                });
                $(elem).typeahead(null, {
                    name: 'best-pictures',
                    display: 'value',
                    source: bestPictures,
                    limit: 10,
                    templates: {
                        suggestion: function(context) {
                            var template = "<div><span class='title'>{{value}}</span>{{group}}</div>";
                            template = template.replace(/\{\{value\}\}/, context.value);

                            var group = "";
                            switch(context.type) {
                                case 'location':
                                    group = "<span class='group-title'>Locations <span class='ion-ios-location'></span></span>";
                                    break;
                                case 'spot':
                                    group = "<span class='group-title'>Spots <span class='ion-ios-information'></span></span>";
                                    break;
                            }
                            if (!context.first) {
                                group = "";
                            }
                            template = template.replace(/\{\{group\}\}/, group);
                            return template;
                        }
                    }
                });
                elem.bind('typeahead:selected', function(obj, datum, name) {
                    console.log('typeahead:selected');
                    scope.$emit('typeahead:selected', datum);
                });
            }
        };
    }
})();