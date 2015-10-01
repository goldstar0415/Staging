(function () {
  'use strict';

  /*
   * Directive to execute function when user press CTRL+ENTER
   */
  angular
    .module('zoomtivity')
    .directive('ctrlEnter', function () {
      return {
        restrict: 'A',
        scope: {
          ctrlEnter: '&'
        },
        link: function (scope, elem, attrs) {
          elem.on('keydown', function (event) {
            var code = event.keyCode || event.which;

            if (code === 13 && event.ctrlKey) {
              if (!event.shiftKey) {
                scope.ctrlEnter();
              }
            }
          });
        }
      }
    });
})();
