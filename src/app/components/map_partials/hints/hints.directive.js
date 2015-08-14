(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('hintsPopup', hintsPopup);

  /** @ngInject */
  function hintsPopup() {
    return {
      restrict: 'E',
      templateUrl: 'app/components/map_partials/hints/tools-hints.html',
      controller: HintsPopupController,
      controllerAs: 'ConfirmPopup'
    };
  }

  function HintsPopupController($scope) {
    $scope.currentLayer = 'path';
  }
})();
