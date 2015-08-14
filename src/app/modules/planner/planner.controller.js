(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlannerController', PlannerController);

  /** @ngInject */
  function PlannerController(Plan, API_URL, $state) {
    var vm = this;
    vm.sourceEvents = [
      [{
        "id": 6521,
        "title": "Artem Onoprienko has a birthday",
        "start": "2015-08-13",
        "birthday": "1988-08-13",
        "type": "user",
        "url": "http:\/\/zoomzoom.itechhighway.com\/user\/6521",
        "avatar": {
          "thumb": "\/images\/avatar.png",
          "medium": "\/images\/avatar.png",
          "large": "\/images\/avatar.png",
          "full": "\/images\/avatar.png"
        },
        "years": "27",
        "events_count": 2,
        "followings_count": 7
      }],
      getEvents,
      eventRender
    ];

    vm.calendarConfig = {
      height: 450,
      //editable: true,
      header: {
        left: 'month agendaWeek agendaDay',
        center: 'title',
        right: 'today prev,next'
      },
      eventLimit: true
      //events: getEvents,
      //eventRender: eventRender
      //eventClick: vm.alertOnEventClick,
      //eventDrop: vm.alertOnDrop,
      //eventResize: vm.alertOnResize,
      //eventRender: vm.eventRender
    };

    function getEvents(start, end, timezone, callback) {
      console.log(arguments);
      Plan.events({
        start_date: start.format('YYYY-MM-DD'),
        end_date: end.format('YYYY-MM-DD')
      }, function success(events) {
        //_.each(events, function (event) {
        //  event.url = $state.href('spot', {spot_id: event.id})
        //});
        events = _.union(events.plans, events.spots);
        console.log(events);
        callback(events);
        //vm.events = data;
      });
    }

    function eventRender(start, end, timezone, callback) {
      console.log(arguments);

      var s = new Date(start).getTime() / 1000;
      var e = new Date(end).getTime() / 1000;
      var m = new Date(start).getMonth();
      var events = [{
        title: 'Feed Me ' + m,
        start: s + (50000),
        end: s + (100000),
        allDay: false,
        className: ['customFeed']
      }];
      callback(events);
    }
  }
})();
