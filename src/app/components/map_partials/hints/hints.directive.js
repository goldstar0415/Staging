(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('hintsPopup', hintsPopup);

  /** @ngInject */
  function hintsPopup() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/map_partials/hints/tools-hints.html',
      controller: HintsPopupController,
      controllerAs: 'ConfirmPopup'
    };
  }

  function HintsPopupController($scope, $state, $rootScope) {
    $scope.showHintPopup = true;

    //if($state.params.area_id) {
    //  $scope.showHintPopup = false;
    //}

    $scope.currentLayer = 'path';

    $scope.LassoSelectionTool = function() {
        angular.element('.lasso-selection').trigger('click');
        $scope.showHintPopup = false;
    };
    $scope.PathSelectionTool = function() {
        angular.element('.path-selection').trigger('click');
        $scope.showHintPopup = false;
    };
    $scope.RadiusSelectionTool = function() {
        angular.element('.radius-selection').trigger('click');
        $scope.showHintPopup = false;
    };

    $scope.close = function () {
      $rootScope.hideHints = true;
      $scope.showHintPopup = false;
    };
  }
})();
