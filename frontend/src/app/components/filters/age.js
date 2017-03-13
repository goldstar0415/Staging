(function () {
  'use strict';

  /*
   * Filter for return number of years
   */
  angular
    .module('zoomtivity')
    .filter('age', function () {
      return function (input) {
        return moment().diff(input, 'years');
      }
    })

})();


