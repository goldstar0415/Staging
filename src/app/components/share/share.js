(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('share', share);

  /** @ngInject */
  function share(API_URL) {
    return {
      restrict: 'E',
      templateUrl: '/app/components/share/share.html',
      scope: {
        links: '='
      }
    };

  }
})();
