(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('confirmPopup', confirmPopup);

  /** @ngInject */
  function confirmPopup() {
    return {
      restrict: 'E',
      templateUrl: 'app/components/map_popups/confirm_box/confirm.html',
      controller: ConfirmPopupController,
      controllerAs: 'ConfirmPopup'
    };
  }

  function ConfirmPopupController($scope) {
  }
})();
