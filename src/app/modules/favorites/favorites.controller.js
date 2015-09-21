(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FavoritesController', FavoritesController);

  /** @ngInject */
  function FavoritesController($scope, $rootScope, Spot, ScrollService, SpotService, allSpots, MapService) {
    var vm = this;
    vm.spots = {};

    vm.markersSpots = formatSpots(allSpots.data);
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.markersSpots = ShowMarkers(vm.markersSpots);

    vm.removeFromFavorite = UnFavorite;

    function UnFavorite(spot, idx) {
      SpotService.removeFromFavorite(spot, function () {
        vm.spots.data.splice(idx, 1);
        if (vm.markersSpots[idx].marker) {
          MapService.GetCurrentLayer().removeLayer(vm.markersSpots[idx].marker);
        } else {
          MapService.GetCurrentLayer().removeLayers(vm.markersSpots[idx].markers)
        }
      })
    }

    function ShowMarkers(spots) {
      var spotsArray = _.map(spots, function (item) {
        return {
          id: item.id,
          spot_id: item.spot_id,
          locations: item.points,
          address: '',
          spot: item
        };
      });

      MapService.drawSpotMarkers(spotsArray, 'other', true);

      return spotsArray;
    }

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
      return _.each(spots, function (spot) {
        SpotService.formatSpot(spot);
      });
    }
  }
})();
