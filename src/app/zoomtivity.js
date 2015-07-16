'use strict';

angular.module('zoomtivity',
  ['ngAnimate',
    'ngCookies',
    'ngTouch',
    'ngSanitize',
    'ngResource',
    'ui.router',
    'ui.bootstrap',
    'snap',
    'ui-notification'])
  .config(function ($stateProvider, $urlRouterProvider) {
    $stateProvider
      .state('index', {
        url: '/'
      });

    $urlRouterProvider.otherwise('/');
  })
  .run(function(MapService){
    MapService.Init('map');
  });

