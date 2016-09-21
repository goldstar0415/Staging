(function () {
    'use strict';

    angular.module('zoomtivity').directive('truncated', [
        function truncated() {
            return {
                restrict: 'A',
                link: function (scope, element, attrs) {
                    scope.$evalAsync(function () {
                        element.dotdotdot();
                    });
                }
            };
        }
    ]);
}());
