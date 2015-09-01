(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FavoritesController', FavoritesController);

  /** @ngInject */
  function FavoritesController($scope, $rootScope, Spot, ScrollService, SpotService) {
    var vm = this;
    vm.spots = {};
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;

    var params = {
      page: 0,
      limit: 10,
      user_id: $rootScope.profileUser.id
    };
    vm.pagination = new ScrollService(Spot.favorites, vm.spots, params);
    $scope.$watch('Favorite.spots.data', function (value) {
      formatSpots(value);
    });

    function formatSpots(spots) {
      _.each(spots, function (spot) {
        SpotService.formatSpot(spot);
      });
    }
  }
})();
