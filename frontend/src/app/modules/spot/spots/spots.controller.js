(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotsController', SpotsController);

  /** @ngInject */
  function SpotsController($rootScope, $scope, Spot, SpotService, MapService, ScrollService, API_URL) {
    var vm = this;
    var isLoadedSpots = false;

    vm.API_URL = API_URL;
    vm.spots = {};
    $rootScope.syncSpots = vm.spots;
    vm.markersSpots = [];
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.removeSpot = removeSpot;
    $rootScope.$on('change-map-state', loadAllSpots);
    $scope.$watch('Spots.spots.data', updateSpots);

    var params = {
      page: 0,
      limit: 30,
      user_id: $rootScope.profileUser.id
    };
    vm.pagination = new ScrollService(Spot.paginate, vm.spots, params);


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
        Spot.query({user_id: $rootScope.profileUser.id}, function (spots) {
          vm.spots.data = spots; //show all spots
          vm.pagination.disabled = true;
        });
        isLoadedSpots = true;
      }
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
          MapService.RemoveMarker(vm.markersSpots[idx].marker);
        } else if (vm.markersSpots[idx].markers) {
          _.each(vm.markersSpots[idx].markers, function (marker) {
            MapService.RemoveMarker(marker);
          });
        }
      });
    }

    //show spots on map
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
  }
})();
