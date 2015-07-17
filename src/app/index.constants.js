/* global malarkey:false, toastr:false, moment:false */
(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .constant('DEBUG', true)
    .constant('API_URL', 'http://localhost.api')
    .constant('SOCKET_URL', 'http://localhost.api:8080')
    .constant('toastr', toastr)
    .constant('moment', moment);

})();
