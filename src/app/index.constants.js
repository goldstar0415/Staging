/* global malarkey:false, toastr:false, moment:false */
(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .constant('DEBUG', true)
    .constant('API_URL', 'http://api.zoomtivity.dev')
    .constant('SOCKET_URL', 'http://api.zoomtivity.dev:8080')
    .constant('toastr', toastr)
    .constant('moment', moment)
    .constant('DATE_FORMAT', {
      datepicker: {
        date: 'm.d.Y',
        time: 'H:i'
      },
      date: 'MM.DD.YYYY',
      time: 'HH:mm'
    })
})();
