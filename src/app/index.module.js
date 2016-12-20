(function () {
  'use strict';

  angular
    .module('zoomtivity', [
      'ngAnimate',
      'ngCookies',
      'ngTouch',
      'ngSanitize',
      'ngResource',
      'ngMessages',
      'ngFileUpload',
      'ui.router',
      'ui.bootstrap',
      'dialogs.main',
      'snap',
      'angular-loading-bar',
      'luegg.directives',
      // 'ui.calendar',
      'ngTagsInput',
      'ui.select',
      'ui.utils.masks',
      'infinite-scroll',
      // 'summernote',
      'angular-carousel',
      'angularjs-dropdown-multiselect',
	    'oc.lazyLoad',
    ])
    .config(['$ocLazyLoadProvider', function ($ocLazyLoadProvider) {

      $ocLazyLoadProvider.config({
        debug: true,
        modules: [
          {name: 'turf', files: [
            '/assets/libs/turf/turf.min.js',
          ]},
          {name: 'calendar', files: [
            'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.7.3/fullcalendar.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.7.3/fullcalendar.min.css',
            'https://cdnjs.cloudflare.com/ajax/libs/angular-ui-calendar/1.0.0/calendar.min.js',
          ]},
          {name: 'gmaps', files: [
            {type: 'js', path: 'https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAytote4odQOn_IpNkj207MXG2bF1uM2Zs'},
          ]},
          {name: 'uploader', files: [
            // todo
          ]},
          {name: 'summernote', files: [
            '/assets/libs/summernote/summernote.js',
            '/assets/libs/summernote/plugin/specialchars/summernote-ext-specialchars.js',
            '/assets/libs/summernote/plugin/specialchars/summernote-ext-specialchars.min.js',
            '/assets/libs/summernote/plugin/databasic/summernote-ext-databasic.js',
            '/assets/libs/summernote/plugin/databasic/summernote-ext-databasic.min.js',
            '/assets/libs/summernote/plugin/hello/summernote-ext-hello.js',
            '/assets/libs/summernote/plugin/hello/summernote-ext-hello.min.js',
            '/assets/libs/summernote/summernote.css',
            '/assets/libs/summernote/plugin/databasic/summernote-ext-databasic.css',
            '/assets/libs/summernote/plugin/databasic/summernote-ext-databasic.min.css',
            '/app/modules/summernote/summernote.js',
          ]},
          {name: 'socket.io', files: [
            '/assets/libs/socket.io/socket.io.js',
          ]}
        ]
      });

    }]);


})();
