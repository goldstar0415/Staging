(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('preloader', Preloader);

  /** @ngInject */
  function Preloader() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/preloader/preloader.html'
    };

  }
})();
