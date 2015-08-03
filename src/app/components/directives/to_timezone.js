(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('toTimezone', function () {
      return function (input, format) {
        format = format || 'MMM DD, YYYY H:mm A';
        var utcOffset = moment().utcOffset();
        return moment(input).add(utcOffset, 'm').format(format);
      }
    })

})();


