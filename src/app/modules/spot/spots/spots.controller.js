(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotsController', SpotsController);

  /** @ngInject */
  function SpotsController($rootScope, $scope, Spot, SpotService, MapService, ScrollService, API_URL, allSpots) {
    var vm = this;
    vm.API_URL = API_URL;
    vm.spots = {};
    $rootScope.syncSpots = vm.spots;
    vm.markersSpots = formatSpots(allSpots);
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.removeSpot = removeSpot;
    vm.markersSpots = ShowMarkers(vm.markersSpots);

    var params = {
      page: 0,
      limit: 10,
      user_id: $rootScope.profileUser.id
    };
    vm.pagination = new ScrollService(Spot.paginate, vm.spots, params);
    $scope.$watch('Spots.spots.data', function (value) {
      value = formatSpots(value);
    });

    function formatSpots(spots) {
      return _.each(spots, function (spot) {
        SpotService.formatSpot(spot);
      });
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
      console.log(spotsArray);
      MapService.drawSpotMarkers(spotsArray, 'other', true);
      MapService.FitBoundsOfCurrentLayer();

      return spotsArray;
    }
  }
})();
