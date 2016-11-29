(function () {
    'use strict';

    angular.module('zoomtivity').directive('truncated', ['$timeout',
        function truncated($timeout) {
            return {
                restrict: 'A',
                scope: {
                    text: '='
                },
                link: function(scope, element, attributes) {
                    //console.log(scope);
                    //console.log(element);
                    scope.$watch(attributes.dotdotdot, function() {
                        $timeout(function() {
                            element.dotdotdot();
                            $(window).resize(function() {
                                element.context.innerText = scope.text;
                                element.dotdotdot();
                            });
                        }, 400);
                    });
                }
            };
        }
    ]);
}());
