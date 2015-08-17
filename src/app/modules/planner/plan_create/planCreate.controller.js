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
    vm.deleteSpot = deleteSpot;
    vm.deleteActivity = deleteActivity;
    vm.addActivity = addActivity;
    vm.spots = [];
    vm.activities = [];

    function save(form) {
      if (form.$valid) {
        vm.$save(function (resp){
          console.log(resp);
        });
      }
    }

    function addActivity() {
      vm.activities.push({});
    }

    function deleteSpot(idx) {
      vm.spots.splice(idx, 1)
    }

    function deleteActivity(idx) {
      vm.activities.splice(idx, 1)
    }
  }
})();
