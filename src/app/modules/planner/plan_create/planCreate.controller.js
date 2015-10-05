(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlanCreateController', PlanCreateController);

  /** @ngInject */
  function PlanCreateController($scope, Plan, plan, categories, $state, DATE_FORMAT) {
    var vm = this;
    vm = _.extend(vm, plan);
    vm.attachments = [];
    vm.newSpots = [];
    vm.categories = categories;

    vm.save = save;
    vm.addActivity = addActivity;
    vm.deleteAttachment = deleteAttachment;

    if (vm.id) {
      formatPlan();
    }

    $scope.$watch('Plan.newSpots.length', addSpots);

    //submit form
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

    //convert data before save
    function _convertData() {
      var data = angular.copy(vm);
      data = _convertDates(data);
      data.activities = [];
      data.spots = [];
      _.each(data.attachments, function (attachment, idx) {
        if (attachment.type == 'activity') {
          var activity = _convertDates(attachment.data);
          activity.activity_category_id = activity.category.id;
          activity.position = idx;
          delete activity.category;
          data.activities.push(activity);
        } else {
          data.spots.push({
            id: attachment.data.id,
            position: idx
          })
        }
      });

      return data;
    }

    function _convertDates(data) {
      data.start_date = moment(data.start_date + ' ' + data.start_time, DATE_FORMAT.date + ' ' + DATE_FORMAT.datepicker.time).format(DATE_FORMAT.backend);
      data.end_date = moment(data.end_date + ' ' + data.end_time, DATE_FORMAT.date + ' ' + DATE_FORMAT.datepicker.time).format(DATE_FORMAT.backend);
      delete data.start_time;
      delete data.end_time;
      return data;
    }

    function _convertTime(data) {
      data.start_time = data.start_date;
      data.end_time = data.end_date;
    }

    //add activity to plan attachments
    function addActivity() {
      vm.attachments.push({
        type: 'activity',
        data: {}
      });
    }

    //add spot to plan attachments
    function addSpots() {
      if (vm.newSpots && vm.newSpots.length > 0) {
        _.each(vm.newSpots, function (spot) {
          vm.attachments.push({
            type: 'spot',
            data: spot
          });
        });

        vm.newSpots = [];
      }
    }

    //delete attachment
    function deleteAttachment(idx) {
      vm.attachments.splice(idx, 1);
    }

    //format data on edit plan
    function formatPlan() {
      var attachments = [];
      _convertTime(vm);
      _.each(vm.spots, function (spot) {
          attachments[spot.pivot.position] = {
            type: 'spot',
            data: spot
          };
        }
      );
      _.each(vm.activities, function (activity) {
          _convertTime(activity);
          attachments[activity.position] = {
            type: 'activity',
            data: activity
          };
        }
      );
      vm.attachments = attachments;
    }

  }
})
();
