(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('age', function () {
      return function (input) {
        return moment().diff(input, 'years');
      }
    })

})();


