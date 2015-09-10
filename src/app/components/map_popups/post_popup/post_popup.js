(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('postPopup', postPopup);

  /** @ngInject */
  function postPopup() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/map_popups/post_popup/post_popup.html',
      scope: {
        data: '=post',
        marker: '='
      }
    };
  }

  function PostPopupController($scope) {

  }
})();
