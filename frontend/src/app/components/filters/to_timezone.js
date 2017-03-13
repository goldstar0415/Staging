(function () {
  'use strict';

  /*
   * Convert date to user timezone
   */
  angular
    .module('zoomtivity')
    .filter('toTimezone', function (DATE_FORMAT) {
      return function (input, format) {
        format = format || DATE_FORMAT.full;
          window.utcOffset = window.utcOffset || moment().utcOffset();
        return moment(input, DATE_FORMAT.backend).add(window.utcOffset, 'm').format(format);
      }
    })

})();


