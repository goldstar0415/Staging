(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotsController', SpotsController);

  /** @ngInject */
  function SpotsController($rootScope, $scope, Spot, SpotService, MapService, ScrollService) {
    var vm = this;
    vm.spots = {};
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.removeSpot = function(spot, idx) {
      SpotService.removeSpot(spot, idx, function() {
        console.log('callback');
        vm.spots.data.splice(idx, 1);
      });
    };
    ShowMarkers(vm.spots.data);

    var params = {
      page: 0,
      limit: 10,
      user_id: $rootScope.profileUser.id
    };
    vm.pagination = new ScrollService(Spot.query, vm.spots, params);
    $scope.$watch('Spots.spots.data', function (value) {
      value = formatSpots(value);
    });

    function formatSpots(spots) {
      return _.each(spots, function (spot) {
        SpotService.formatSpot(spot);
      });
    }
    function ShowMarkers(spots) {
      var spotsArray = _.map(spots, function(item) {
        return {
          id: item.id,
          spot_id: item.spot_id,
          locations: item.points,
          address: '',
          spot: item
        };
      });
      MapService.drawSpotMarkers(spotsArray, 'other', true);
    }
  }
})();
