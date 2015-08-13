(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlannerController', PlannerController);

  /** @ngInject */
  function PlannerController(Plan) {
    var vm = this;
    vm.events = [];

    vm.calendarConfig = {
      height: 450,
      //editable: true,
      header: {
        left: 'month agendaWeek agendaDay',
        center: 'title',
        right: 'today prev,next'
      },
      eventLimit: true,
      //events: getEvents,
      eventRender: eventRender
      //eventClick: vm.alertOnEventClick,
      //eventDrop: vm.alertOnDrop,
      //eventResize: vm.alertOnResize,
      //eventRender: vm.eventRender
    };

    function getEvents(start, end, timezone, callback) {
      Plan.events({
        start: start,
        end: end
      }, function success(data) {
        callback(events);
        //vm.events = data;
      });
    }

    function eventRender(data, element) {
      console.log(arguments);
      var description;
      if (data.type == 'event' || data.type == 'plan') {
        description = {
          text: data.description,
          title: data.title
        };
        if (data.type == 'plan') {
          $(element).css('border-color', '#28ce9d');
          $(element).css('background-color', '#28ce9d');
        }
      } else if (data.type == 'user') {
        description = data.title;
      }

      //Attach tooltip
      $(element).qtip({
        overwrite: true,
        content: description,
        //Position config
        position: {
          my: 'top center',
          at: 'bottom center'
        },
        //Show config
        show: {
          event: 'click mouseenter',
          solo: true
        },
        //Style config
        style: {
          classes: 'qtip-light qtip-shadow'
        }
      });
    }
  }
})();
