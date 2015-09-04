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
        item: '=',
        type: '@'
      },
      link: ShareLink
    };

    function ShareLink(s, e, a) {
      if (s.type == 'spot') {
        s.link = API_URL + '/';
      }
    }
  }
})();
