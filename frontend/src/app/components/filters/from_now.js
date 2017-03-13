(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('fromNow', function (DATE_FORMAT) {
      return function (input) {
        window.utcOffset = window.utcOffset || moment().utcOffset();
        return moment(input, DATE_FORMAT.backend).add(window.utcOffset, 'm').fromNow();
      }
    })

})();


