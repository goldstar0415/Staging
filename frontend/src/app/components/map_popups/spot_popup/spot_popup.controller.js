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
    $scope.commentIndex = 0;

    $scope.saveToCalendar = SpotService.saveToCalendar;
    $scope.removeFromCalendar = SpotService.removeFromCalendar;
    $scope.addToFavorite = SpotService.addToFavorite;
    $scope.removeFromFavorite = SpotService.removeFromFavorite;
    $scope.changeComment = changeComment;
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

    function changeComment(step) {
      var nextIndex = $scope.commentIndex + step;
      if (nextIndex >= 0 && nextIndex < $scope.data.spot.comments.length) {
        $scope.commentIndex = nextIndex;
      }
    }

    function changePhoto(step) {
      var nextIndex = $scope.photoControl.start + step;
      if (nextIndex >= 0 && nextIndex + $scope.photoControl.step <= $scope.data.spot.photos.length) {
        $scope.photoControl.start = nextIndex;
      }
    }

    function isEmptyAttachments() {
      var comment = $scope.data.spot.comments[$scope.commentIndex].attachments;
      if (comment) {
        return comment.spots.length == 0 && comment.album_photos.length == 0 && comment.areas.length == 0 && comment.links.length == 0;
      }
    }

  }
})();
