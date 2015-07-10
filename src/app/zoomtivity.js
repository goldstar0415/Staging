'use strict';

angular.module('zoomtivity',
  ['ngAnimate',
    'ngCookies',
    'ngTouch',
    'ngSanitize',
    'ngResource',
    'ui.router',
    'ui.bootstrap'])
  .config(function ($stateProvider, $urlRouterProvider) {
    $stateProvider
      .state('index', {
        url: '/'
      });

    $urlRouterProvider.otherwise('/');
  })
  .run(function(MapService){
    var map = MapService.Init('map', {}, '');
  });
