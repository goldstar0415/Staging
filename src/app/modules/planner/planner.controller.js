(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlannerController', PlannerController);

  /** @ngInject */
  function PlannerController($rootScope, Plan, $state, $compile, $scope, all_plans, dialogs, ScrollService, API_URL, DATE_FORMAT, MapService) {
    var vm = this;
    vm.API_URL = API_URL;
    var displayPlans = all_plans.data;
    var markers = [];

    vm.plans = {};
    vm.deletePlan = deletePlan;
    vm.sourceEvents = [
      [],
      getEvents
    ];
    var params = {
      page: 0,
      limit: 10
    };
    vm.pagination = new ScrollService(Plan.query, vm.plans, params);
    vm.calendarConfig = {
      height: 450,
      //editable: true,
      header: {
        left: 'month agendaWeek agendaDay',
        center: 'title',
        right: 'today prev,next'
      },
      eventLimit: false,
      //eventClick: vm.alertOnEventClick,
      //eventDrop: vm.alertOnDrop,
      //eventResize: vm.alertOnResize,
      eventRender: eventRender
    };


    //show events on calendar
    function getEvents(start, end, timezone, callback) {
      Plan.events({
        start_date: start.format('YYYY-MM-DD'),
        end_date: end.format('YYYY-MM-DD')
      }, function success(events) {
        _.each(events, function (type, key) {
          _.each(type, function (event) {
            if (key == 'spots') {
              event.url = $state.href('spot', {user_id: event.user_id, spot_id: event.id});
            } else if (key == 'plans') {
              event.url = $state.href('planner.view', {user_id: event.user_id, plan_id: event.id});
            }

            if (event.start_date) {
              event.start =  moment(event.start_date, DATE_FORMAT.backend).toDate();
            }
            if (event.end_date) {
              event.end = moment(event.end_date, DATE_FORMAT.backend).toDate();
            }
          });
        });

        events = _.union(events.plans, events.spots);
        console.log(events);
        callback(events);
      });
    }

    function eventRender(event, element, view) {
      var tooltip = moment(event.start_date, DATE_FORMAT.backend).format(DATE_FORMAT.full)
        + '\n - \n' + moment(event.end_date, DATE_FORMAT.backend).format(DATE_FORMAT.full);
      element.attr({
        'tooltip': tooltip,
        'tooltip-append-to-body': true
      });
      $compile(element)($scope);
    }

    /*
     * Delete plan
     * @param plan {Plan}
     * @param idx {number} plan index
     */
    function deletePlan(plan, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete plan?').result.then(function () {
        Plan.delete({id: plan.id});
        for (var k in markers) {
          if (markers[k].id == plan.id) {
            MapService.RemoveMarker(markers[k].marker);
            break;
          }
        }
        vm.plans.data.splice(idx, 1);

      });
    }

    //show plan markers
    function InitMap() {
      _.each(displayPlans, function (plan) {
        var marker = CreateMarker($rootScope.plannerIcon, plan.title, plan.id, plan.location);
        markers.push({id: plan.id, marker: marker});

        _.each(plan.activities, function (activity) {
          var activityMarker = CreateMarker(activity.category.icon_url, activity.title, plan.id, activity.location);
          markers.push({id: plan.id, marker: activityMarker});
        });

        _.each(plan.spots, function (spot) {
          var spotMarker = CreateMarker(spot.category.icon_url, spot.title, plan.id, spot.points[0].location);
          markers.push({id: plan.id, marker: spotMarker});
        });
      });

      MapService.FitBoundsOfCurrentLayer();
    }

    //create marker on map
    function CreateMarker(iconUrl, title, plan_id, location) {

      var icon = MapService.CreateCustomIcon(iconUrl, 'planner-icon');
      var options = {};

      if (icon) options.icon = icon;
      if (title) options.title = title;

      var marker = MapService.CreateMarker(location, options);
      marker.on('click', function () {
        $state.go('planner.view', {user_id: $rootScope.currentUser.id, plan_id: plan_id});
      });

      return marker;
    }

    InitMap();
  }
})();
