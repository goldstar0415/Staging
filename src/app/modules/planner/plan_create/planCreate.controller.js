(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlanCreateController', PlanCreateController);

  /** @ngInject */
  function PlanCreateController(plan) {
    var vm = this;
    vm = _.extend(vm, plan);
    vm.save = save;

    function save(form) {
      if (form.$valid) {
        vm.$save(function (resp){
          console.log(resp);
        });
      }
    }
  }
})();
