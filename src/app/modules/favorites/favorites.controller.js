(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FavoritesController', FavoritesController);

  /** @ngInject */
  function FavoritesController($scope, $rootScope, Spot, ScrollService, SpotService, allSpots, MapService, API_URL) {
    var vm = this;
    vm.API_URL = API_URL;

    vm.spots = {};

    vm.markersSpots = formatSpots(allSpots.data);
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.markersSpots = ShowMarkers(vm.markersSpots);
    vm.removeSpot = removeSpot;

    vm.removeFromFavorite = UnFavorite;

    /*
     * Delete spot from favorites
     * @param spot {Spot}
     * @param idx {number} spot index
     */
    function UnFavorite(spot, idx) {
      SpotService.removeFromFavorite(spot, function () {
        if ($rootScope.currentUser.id == $rootScope.profileUser.id) {
          vm.spots.data.splice(idx, 1);
          if (vm.markersSpots[idx].marker) {
            MapService.GetCurrentLayer().removeLayer(vm.markersSpots[idx].marker);
          } else {
            MapService.GetCurrentLayer().removeLayers(vm.markersSpots[idx].markers)
          }
        }
      })
    }

    /*
     * Show markers on map
     * @param spots {Spot}
     */
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
      MapService.FitBoundsOfCurrentLayer();

      return spotsArray;
    }

    /*
     * Delete spot
     * @param spot {Spot}
     * @param idx {number} spot index
     */
    function removeSpot(spot, idx) {
      SpotService.removeSpot(spot, idx, function () {
        vm.spots.data.splice(idx, 1);
        if (vm.markersSpots[idx].marker) {
          MapService.GetCurrentLayer().removeLayer(vm.markersSpots[idx].marker);
        } else {
          MapService.GetCurrentLayer().removeLayers(vm.markersSpots[idx].markers)
        }
      });
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
