(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('spotPopup', spotPopup);

  /** @ngInject */
  function spotPopup() {
    return {
      restrict: 'E',
      templateUrl: 'app/components/map_popups/spot_popup/spot_popup.html',
      controller: SpotPopupController,
      controllerAs: 'SpotPopup',
      scope: {
        data: '=spot',
        marker: '='
      }
    };
  }

  function SpotPopupController($scope) {
    $scope.view = 'about';
    $scope.showNextPhoto = false;
    $scope.showPrevPhoto = false;

    $scope.showNextReview = false;
    $scope.showPrevReview = false;

    $scope.marker.on('click', function() {
      if($scope.data.spot.reviews.length > 0) {
        $scope.currentReview = $scope.data.spot.reviews[0];
      }
      $scope.view = 'about';
      $scope.$apply();
    });

    $scope.nextPhoto = function() {

    };

    $scope.prevPhoto = function() {

    };

    $scope.nextReview = function() {

    };

    $scope.prevReview = function() {

    };
  }
})();
