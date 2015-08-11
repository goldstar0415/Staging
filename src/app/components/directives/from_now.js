(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('fromNow', function () {
      return function (input) {
        var utcOffset = moment().utcOffset();
        return moment(input).add(utcOffset, 'm').fromNow();
      }
    })

})();


