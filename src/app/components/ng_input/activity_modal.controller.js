(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ActivityModalController', ActivityModalController);

  /** @ngInject */
  function ActivityModalController(spots, attachments, $modalInstance) {
    var vm = this;
    vm.tab = 'events';
    vm.spots = spots;
    vm.attachments = attachments;

    vm.addSpot = function (spot) {
      if (_.findWhere(vm.attachments.spots, {id: spot.id})) {
        vm.attachments.spots = _.reject(vm.attachments.spots, function (item) {
          return item.id == spot.id;
        });
      } else {
        vm.attachments.spots.push(spot);
      }

      spot.selected = !spot.selected;
    }

  }
})();
