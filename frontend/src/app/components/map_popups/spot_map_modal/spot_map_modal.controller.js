(function () {
  'use strict';

  /*
   * Controller for spot modal on mobile instead of spot popup
   */
  angular
    .module('zoomtivity')
    .controller('SpotMapModalController', SpotMapModalController);

  /** @ngInject */
  function SpotMapModalController($scope, spot, marker, SpotService, $modalInstance, API_URL, SpotComment) {
    $scope.data = {spot: spot};
    $scope.marker = marker;
    $scope.close = close;

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
      $scope.data.spot.photos = _.union($scope.data.spot.photos, $scope.data.spot.comments_photos);

      var params = {
        page: 1,
        limit: 10,
        spot_id: $scope.data.spot.id
      };
      SpotComment.query(params, function (comments) {
        $scope.data.spot.comments = comments.data;
      });
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
      var commemnt = $scope.data.spot.comments[$scope.commentIndex].attachments;
      if (commemnt) {
        return commemnt.spots.length == 0 && commemnt.album_photos.length == 0 && commemnt.areas.length == 0 && commemnt.links.length == 0;
      }
    }

    function close() {
      $modalInstance.close();
    }
  }
})();
