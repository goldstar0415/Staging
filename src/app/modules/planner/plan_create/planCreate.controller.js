(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlanCreateController', PlanCreateController);

  /** @ngInject */
  function PlanCreateController(Plan, plan, categories, $state) {
    var vm = this;
    vm = _.extend(vm, plan);
    console.log(vm);
    vm.spots = vm.spots || [];
    vm.activities = vm.activities || [];
    vm.categories = categories;

    vm.save = save;
    vm.deleteSpot = deleteSpot;
    vm.deleteActivity = deleteActivity;
    vm.addActivity = addActivity;

    function save(form) {
      if (form.$valid) {
        if (vm.id) {
          Plan.update(_convertData(), function (resp) {
            $state.go('planner.list');
          });
        } else {
          Plan.save(_convertData(), function (resp) {
            $state.go('planner.list');
          });
        }
      }
    }

    function _convertData() {
      var data = angular.copy(vm);
      data = _convertDates(data);
      data.spots = _.pluck(data.spots, 'id');

      _.each(data.activities, function (activity) {
        activity = _convertDates(activity);
        activity.activity_category_id = activity.category.id;
        delete activity.category;
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
