(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .config(routeConfig);

  /** @ngInject */
  function routeConfig($stateProvider, $urlRouterProvider) {
    $stateProvider
      .state('settings', {
        url: '/settings',
        templateUrl: 'app/settings/settings.html',
        controller: 'SettingsController',
        controllerAs: 'Settings',
        mapState: 'hidden'
      })
    ;

    $urlRouterProvider.otherwise('/');
  }

})();
