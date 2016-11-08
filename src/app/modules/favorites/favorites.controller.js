(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FavoritesController', FavoritesController);

  /** @ngInject */
  function FavoritesController($scope, $rootScope, Spot, ScrollService, SpotService, MapService, API_URL) {
    var vm = this;
    var isLoadedSpots = false;

    vm.API_URL = API_URL;
    vm.spots = {};
    vm.markersSpots = [];

    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeSpot = removeSpot;
    vm.removeFromFavorite = UnFavorite;
    $rootScope.$on('change-map-state', loadAllSpots);
    $scope.$watch('Favorite.spots.data', updateSpots);

    var params = {
      page: 0,
      limit: 9,
      user_id: $rootScope.profileUser.id
    };
    vm.pagination = new ScrollService(Spot.favorites, vm.spots, params);

    function updateSpots(value, old) {
      value = formatSpots(value);

      if (old && old.length > 0) {
        var newSpots = _.select(value, function (item) {
          return !_.findWhere(old, {id: item.id});
        });

        vm.markersSpots = _.union(vm.markersSpots, ShowMarkers(newSpots));
      } else {
        vm.markersSpots = _.union(vm.markersSpots, ShowMarkers(value));
      }
    }

    function formatSpots(spots) {
      return _.each(spots, function (spot) {
        SpotService.formatSpot(spot);
      });
    }

    function loadAllSpots(e, mapState) {
      if (mapState == 'big' && !isLoadedSpots) {
        Spot.favorites({user_id: $rootScope.profileUser.id}, function (spots) {
          vm.spots.data = spots.data; //show all spots
          vm.pagination.disabled = true;
        });
        isLoadedSpots = true;
      }
    }

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

      MapService.drawSpotMarkers(spotsArray, 'other', false);
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
  }
})();
