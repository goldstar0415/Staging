(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlannerController', PlannerController);

  /** @ngInject */
  function PlannerController(Plan, $state, $compile, $scope) {
    var vm = this;
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
        _.each(events.spots, function (event) {
          event.url = $state.href('spot', {spot_id: event.id});
          event.start = event.start_date;
          event.end = event.end_date;
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
  }
})();
