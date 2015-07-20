(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('ngMatch', function ($parse) {
      return {
        require: '?ngModel',
        restrict: 'A',
        link: function (scope, elem, attrs, ctrl) {
          if (!ctrl) {
            if (console && console.warn) {
              console.warn('Match validation requires ngModel to be on the element');
            }
            return;
          }

          var matchGetter = $parse(attrs.ngMatch);

          scope.$watch(getMatchValue, function () {
            ctrl.$$parseAndValidate();
          });

          ctrl.$validators.match = function () {
            return ctrl.$viewValue === getMatchValue();
          };

          function getMatchValue() {
            var match = matchGetter(scope);
            console.log(match);
            if (angular.isObject(match) && match.hasOwnProperty('$viewValue')) {
              match = match.$viewValue;
            }
            return match;
          }
        }
      };
    }
  )
  ;

})();
