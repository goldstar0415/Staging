(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlanController', PlanController);

  /** @ngInject */
  function PlanController(plan) {
    var vm = this;
    vm = _.extend(vm, plan);

  }
})();
