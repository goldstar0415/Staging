/* global malarkey:false, toastr:false, moment:false */
(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .constant('DEBUG', true)
    .constant('MOBILE_APP', false)
    .constant('BACKEND_URL', 'https://api.zoomtivity.com')
    .constant('API_URL', 'https://api.zoomtivity.com')
    .constant('SOCKET_URL', 'https://api.zoomtivity.com:8080')
    .constant('GOOGLE_API_KEY', 'AIzaSyBbBdjAuH8wCJsLBThDXYRBYX9e45Dyf_8')
    .constant('GOOGLE_CLIENT_ID', '499164554835-tnvq6glehrsnf73he3u353jaqn5ikpc8.apps.googleusercontent.com')
    .constant('JS_CONSOLE_KEY', 'FASSX1CD-12A0-4SD3-AE36-757BVB26SBEZX')
    .constant('GEOCODING_KEY', 'peVrdYeAJADUMTIMKXuK4j9G52cnsY8p')
	.constant('MAPBOX_API_KEY', 'pk.eyJ1IjoiaW5ndmFyIiwiYSI6ImNpcWwwOTFsbjAwMm9pd20yYmhlMDloc3gifQ.hEl195NS4jCsaDxSq2rEyA')
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
      planner_date: 'MMM DD, YYYY',
      backend: 'YYYY-MM-DD HH:mm:ss',
      backend_date: 'YYYY-MM-DD'
    })

})();
