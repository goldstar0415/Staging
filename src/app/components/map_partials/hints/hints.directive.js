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
    $scope.currentLayer = 'path';

    var latlngPoint = new L.LatLng(1,1);

    $scope.LassoSelectionTool = function () {
      //angular.element('.lasso-selection').trigger('click');
      var control = L.Control.Lasso();
      control._click({
        latlng: latlngPoint,
        layerPoint: map.latLngToLayerPoint(latlngPoint),
        containerPoint: map.latLngToContainerPoint(latlngPoint)
      });
      $scope.showHintPopup = false;
    };
    $scope.PathSelectionTool = function () {
      //angular.element('.path-selection').trigger('click');
      var control = L.Control.Path();
      control._click({
        latlng: latlngPoint,
        layerPoint: map.latLngToLayerPoint(latlngPoint),
        containerPoint: map.latLngToContainerPoint(latlngPoint)
      });
      $scope.showHintPopup = false;
    };
    $scope.RadiusSelectionTool = function () {
      //angular.element('.radius-selection').trigger('click');
      var control = L.Control.Radius();
      control._click({
        latlng: latlngPoint,
        layerPoint: map.latLngToLayerPoint(latlngPoint),
        containerPoint: map.latLngToContainerPoint(latlngPoint)
      });
      $scope.showHintPopup = false;
    };

    $scope.close = function () {
      $rootScope.hideHints = true;
      $scope.showHintPopup = false;
    };
  }
})();
