(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlanCreateController', PlanCreateController);

  /** @ngInject */
  function PlanCreateController(Plan, plan, $state) {
    var vm = this;
    vm = _.extend(vm, plan);
    vm.save = save;
    vm.deleteSpot = deleteSpot;
    vm.deleteActivity = deleteActivity;
    vm.addActivity = addActivity;
    vm.spots = [];
    vm.activities = [];
    vm.categories = [{"id": 1, "spot_type_id": 1, "name": "praesentium", "display_name": "Praesentium"}];

    function save(form) {
      if (form.$valid) {
        Plan.save(_convertData(), function (resp) {
            console.log(resp);
          $state.go('planner.list');
          });
      }
    }

    function _convertData() {
      var data = angular.copy(vm);
      data = _convertDates(data);
      data.spots = _.pluck(data.spots, 'id');

      _.each(data.activities, function (activity) {
        activity = _convertDates(activity);
      });

      console.log(data);
      return data;
    }

    function _convertDates(data){
      data.start_date = moment(data.start_date, 'MM/DD/YYYY').format('YYYY-MM-DD HH:mm:ss');
      data.end_date = moment(data.end_date, 'MM/DD/YYYY').format('YYYY-MM-DD HH:mm:ss');
      return data;
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
