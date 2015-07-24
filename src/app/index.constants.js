/* global malarkey:false, toastr:false, moment:false */
(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .constant('DEBUG', true)
    .constant('API_URL', 'http://api.zoomtivity')
    .constant('SOCKET_URL', 'http://api.zoomtivity:8080')
    .constant('toastr', toastr)
    .constant('moment', moment)
})();
