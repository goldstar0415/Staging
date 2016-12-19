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
      'ui.calendar',
      'ngTagsInput',
      'ui.select',
      'ui.utils.masks',
      'infinite-scroll',
      'summernote',
      'angular-carousel',
      'angularjs-dropdown-multiselect',
	    'oc.lazyLoad'
    ])
    .config(['$ocLazyLoadProvider', function ($ocLazyLoadProvider) {
      $ocLazyLoadProvider.config({
        debug: true,
        modules: [
          {name: 'turf', files: ['assets/libs/turf/turf.min.js']}
        ]
      });
    }]);


})();
