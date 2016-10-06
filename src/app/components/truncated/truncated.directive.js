(function () {
    'use strict';

    angular.module('zoomtivity').directive('truncated', ['$timeout',
        function truncated($timeout) {
            return {
                restrict: 'A',
                link: function(scope, element, attributes) {
                    scope.$watch(attributes.dotdotdot, function() {
                        $timeout(function() {
                            element.dotdotdot();
                        }, 400);
                    });
                }
            };
        }
    ]);
}());
