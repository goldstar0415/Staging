(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlannerController', PlannerController);

  /** @ngInject */
  function PlannerController(Plan, plans, $state, $compile, $scope, dialogs) {
    var vm = this;
    vm.plans = plans;
    vm.deletePlan = deletePlan;
    vm.sourceEvents = [
      [],
      getEvents
    ];

    vm.calendarConfig = {
      height: 450,
      //editable: true,
      header: {
        left: 'month agendaWeek agendaDay',
        center: 'title',
        right: 'today prev,next'
      },
      eventLimit: true,
      //eventClick: vm.alertOnEventClick,
      //eventDrop: vm.alertOnDrop,
      //eventResize: vm.alertOnResize,
      eventRender: eventRender
    };

    function getEvents(start, end, timezone, callback) {
      Plan.events({
        start_date: start.format('YYYY-MM-DD'),
        end_date: end.format('YYYY-MM-DD')
      }, function success(events) {
        _.each(events, function (type, key) {
          _.each(type, function (event) {
            if (key == 'spots') {
              event.url = $state.href('spot', {spot_id: event.id});
            } else if (key == 'plans') {
              event.url = $state.href('planner.view', {plan_id: event.id});
            }

            if (event.start_date) {
              event.start = event.start_date;
            }
            if (event.end_date) {
              event.end = event.end_date;
            }
          });
        });


        console.log(events);
        events = _.union(events.plans, events.spots);
        callback(events);
      });
    }

    function eventRender(event, element, view) {
      element.attr({
        'tooltip': event.title,
        'tooltip-append-to-body': true
      });
      $compile(element)($scope);
    }

    function deletePlan(plan, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete plan?').result.then(function () {
        Plan.delete({id: plan.id});
        vm.plans.data.splice(idx, 1);
      });
    }
  }
})();
