(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotsController', SpotsController);

  /** @ngInject */
  function SpotsController(spots, SpotService) {
    var vm = this;
    vm.spots = spots;
    vm.spots.data = formatSpots(vm.spots.data);
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.removeSpot = SpotService.removeSpot;

    function formatSpots(spots) {
      _.each(spots, function (spot) {
        SpotService.formatSpot(spot);
      });

      return spots;
    }
  }
})();
