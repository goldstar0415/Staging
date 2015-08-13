(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlannerController', PlannerController);

  /** @ngInject */
  function PlannerController() {
    var vm = this;
    vm.events = [];

    vm.calendarConfig = {
        height: 450,
        editable: true,
        header:{
          left: 'month agendaWeek agendaDay',
          center: 'title',
          right: 'today prev,next'
        },
        eventClick: vm.alertOnEventClick,
        eventDrop: vm.alertOnDrop,
        eventResize: vm.alertOnResize,
        eventRender: vm.eventRender
    };
  }
})();
