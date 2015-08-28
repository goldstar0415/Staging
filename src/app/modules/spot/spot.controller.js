(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotController', SpotController);

  /** @ngInject */
  function SpotController(spot, SpotService, $state, $rootScope) {
    var vm = this;
    vm.spot = SpotService.formatSpot(spot);
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.removeSpot = function(spot, idx) {
      SpotService.removeSpot(spot, idx, function() {
        $state.go('spots', {user_id: $rootScope.currentUser.id});
      });
    };

  }
})();
