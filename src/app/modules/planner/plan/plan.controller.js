(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlanController', PlanController);

  /** @ngInject */
  function PlanController(plan, SpotService) {
    var vm = this;
    vm = _.extend(vm, plan);
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
  }
})();
