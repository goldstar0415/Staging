/* global malarkey:false, toastr:false, moment:false */
(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .constant('DEBUG', true)
    .constant('API_URL', 'http://api.zoomtivity.com')
    .constant('SOCKET_URL', 'http://api.zoomtivity.com:8080')
    .constant('JS_CONSOLE_KEY', 'FASSX1CD-12A0-4SD3-AE36-757BVB26SBEZX')
    .constant('toastr', toastr)
    .constant('moment', moment)
    .constant('DATE_FORMAT', {
      datepicker: {
        date: 'MM.DD.YYYY',
        time: 'h:mm a'
      },
      date: 'MM.DD.YYYY',
      time: 'HH:mm',
      full: 'MMM DD, YYYY H:mm A',
      backend: 'YYYY-MM-DD HH:mm:ss'
    })

})();
