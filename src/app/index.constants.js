/* global malarkey:false, toastr:false, moment:false */
(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .constant('DEBUG', true)
    .constant('MOBILE_APP', false)
    .constant('API_URL', 'http://api.zoomtivity.com')
    .constant('SOCKET_URL', 'http://api.zoomtivity.com:8080')
    .constant('GOOGLE_API_KEY', 'AIzaSyAOSaNuoJQopmIhKJYAVt-_V04nXOZ4jMI')
    .constant('GOOGLE_APP_ID', '499164554835')
    .constant('JS_CONSOLE_KEY', 'FASSX1CD-12A0-4SD3-AE36-757BVB26SBEZX')
    .constant('GEOCODING_KEY', 'peVrdYeAJADUMTIMKXuK4j9G52cnsY8p')
    .constant('toastr', toastr)
    .constant('moment', moment)
    .constant('USER_ONLINE_MINUTE', -5)
    .constant('DATE_FORMAT', {
      datepicker: {
        date: 'MM.DD.YYYY',
        time: 'h:mm a'
      },
      date: 'MM.DD.YYYY',
      time: 'hh:mm a',
      full: 'MMM DD, YYYY H:mm A',
      backend: 'YYYY-MM-DD HH:mm:ss'
    })

})();
