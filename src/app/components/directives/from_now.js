(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('fromNow', function () {
      return function (input) {
        return moment(input).fromNow();
      }
    })

})();


