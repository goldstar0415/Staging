(function () {
  'use strict';

  /*
   * Controller for spot popup
   */
  angular
    .module('zoomtivity')
    .controller('SpotPopupController', SpotPopupController);

  /** @ngInject */
  function SpotPopupController($scope, SpotService, API_URL) {
    $scope.API_URL = API_URL;
    $scope.view = 'about';
    $scope.reviewIndex = 0;

    $scope.saveToCalendar = SpotService.saveToCalendar;
    $scope.removeFromCalendar = SpotService.removeFromCalendar;
    $scope.addToFavorite = SpotService.addToFavorite;
    $scope.removeFromFavorite = SpotService.removeFromFavorite;
    $scope.changeReview = changeReview;
    $scope.changePhoto = changePhoto;
    $scope.isEmptyAttachments = isEmptyAttachments;


    $scope.photoControl = {
      start: 0,
      step: 4
    };

    run();


    ///////

    function run() {
      SpotService.setScope($scope);
    }

    function changeReview(step) {
      var nextIndex = $scope.reviewIndex + step;
      if (nextIndex >= 0 && nextIndex < $scope.data.spot.comments.length) {
        $scope.reviewIndex = nextIndex;
      }
    }

    function changePhoto(step) {
      var nextIndex = $scope.photoControl.start + step;
      if (nextIndex >= 0 && nextIndex + $scope.photoControl.step <= $scope.data.spot.photos.length) {
        $scope.photoControl.start = nextIndex;
      }
    }

    function isEmptyAttachments() {
      var review = $scope.data.spot.comments[$scope.reviewIndex].attachments;
      if (review) {
        return review.spots.length == 0 && review.album_photos.length == 0 && review.areas.length == 0 && review.links.length == 0;
      }
    }

  }
})();
