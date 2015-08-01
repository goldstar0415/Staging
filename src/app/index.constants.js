/* global malarkey:false, toastr:false, moment:false */
(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .constant('DEBUG', true)
    .constant('API_URL', 'http://api.zoomtivity.dev')
    .constant('SOCKET_URL', 'http://localhost:8081/socket')
    .constant('toastr', toastr)
    .constant('moment', moment)
})();
