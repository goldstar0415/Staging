(function () {
  'use strict';

  /*
   * Controller for spot modal on mobile instead of spot popup
   */
  angular
    .module('zoomtivity')
    .controller('SpotMapModalController', SpotMapModalController);

  /** @ngInject */
  function SpotMapModalController($scope, spot, marker, SpotService, $modalInstance, Spot, SpotComment) {
    $scope.data = spot;
    $scope.marker = marker;
    $scope.saveToCalendar = SpotService.saveToCalendar;
    $scope.removeFromCalendar = SpotService.removeFromCalendar;
    $scope.addToFavorite = SpotService.addToFavorite;
    $scope.removeFromFavorite = SpotService.removeFromFavorite;
    $scope.view = 'about';
    $scope.showNextPhoto = false;
    $scope.showPrevPhoto = false;

    $scope.showNextReview = false;
    $scope.showPrevReview = false;

    $scope.nextPhoto = SpotService.mapNextPhoto;
    $scope.prevPhoto = SpotService.mapPrevPhoto;

    $scope.nextReview = SpotService.mapNextReview;
    $scope.prevReview = SpotService.mapPrevReview;

    $scope.close = close;

    run();

    /////////

    function run() {
      SpotService.setScope($scope);

      Spot.get({id:$scope.data.spot.id}, function (fullSpot) {
        //merge photos
        fullSpot.photos = _.union(fullSpot.comments_photos, fullSpot.photos);
        $scope.data.spot = fullSpot;

        var params = {
          page: 1,
          limit: 10,
          spot_id: fullSpot.id
        };
        SpotComment.query(params, function (comments) {
          $scope.data.spot.comments = comments.data;

          SpotService.initMarker();
        });
      });
    }

    function close() {
      $modalInstance.close();
    }
  }
})();
