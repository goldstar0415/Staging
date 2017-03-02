/* global malarkey:false, toastr:false, moment:false */
(function () {
    'use strict';

    if (!window.ENV) {
        throw new Error("Please configure the env.js");
    }

    var env = window.ENV;
    angular
        .module('zoomtivity')
        .constant('DEBUG', env.DEBUG)
        .constant('MOBILE_APP', env.MOBILE_APP)
        .constant('BACKEND_URL', env.BACKEND_URL)
        .constant('API_URL', env.API_URL)
        .constant('SOCKET_URL', env.SOCKET_URL)
        .constant('S3_URL', env.S3_URL)
        .constant('GOOGLE_API_KEY', env.GOOGLE_API_KEY)
        .constant('GOOGLE_API_KEYS_POOL', env.GOOGLE_API_KEYS_POOL)
        .constant('GOOGLE_CLIENT_ID', env.GOOGLE_CLIENT_ID)
        .constant('JS_CONSOLE_KEY', env.JS_CONSOLE_KEY)
        .constant('MAPBOX_API_KEY', env.MAPBOX_API_KEY)
        .constant('SKOBBLER_API_KEY', env.SKOBBLER_API_KEY)
        .constant('toastr', toastr)
        .constant('moment', moment)
        .constant('USER_ONLINE_MINUTE', env.USER_ONLINE_MINUTE)
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
