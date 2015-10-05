(function () {
  'use strict';

  /*
   * Controller for spot popup
   */
  angular
    .module('zoomtivity')
    .controller('SpotPopupController', SpotPopupController);

  /** @ngInject */
  function SpotPopupController($scope, SpotService) {
    $scope.saveToCalendar = SpotService.saveToCalendar;
    $scope.removeFromCalendar = SpotService.removeFromCalendar;
    $scope.addToFavorite = SpotService.addToFavorite;
    $scope.removeFromFavorite = SpotService.removeFromFavorite;
    $scope.view = 'about';
    $scope.showNextPhoto = false;
    $scope.showPrevPhoto = false;

    $scope.showNextReview = false;
    $scope.showPrevReview = false;

    SpotService.setScope($scope);

    SpotService.initMarker();

    $scope.nextPhoto = SpotService.mapNextPhoto;
    $scope.prevPhoto = SpotService.mapPrevPhoto;

    $scope.nextReview = SpotService.mapNextReview;
    $scope.prevReview = SpotService.mapPrevReview;

  }
})();
