(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlanController', PlanController);

  /** @ngInject */
  function PlanController(plan, PlanComment, SpotService, ScrollService, MapService) {
    var vm = this;
    vm = _.extend(vm, plan);
    vm.comments = {};
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.send = send;
    var displayPlans = _.union(plan.activities, plan.spots);

    console.log(displayPlans);

    var params = {
      page: 0,
      limit: 10,
      plan_id: plan.id
    };
    vm.pagination = new ScrollService(PlanComment.query, vm.comments, params);

    function send() {
      PlanComment.save({plan_id: plan.id},
        {
          body: vm.message || '',
          attachments: {
            album_photos: _.pluck(vm.attachments.photos, 'id'),
            spots: _.pluck(vm.attachments.spots, 'id'),
            areas: _.pluck(vm.attachments.areas, 'id')
          }
        }, function success(message) {
          vm.comments.data.unshift(message);
          vm.message = '';
          vm.attachments.photos = [];
          vm.attachments.spots = [];
          vm.attachments.areas = [];
        }, function error(resp) {
          console.log(resp);
          toastr.error('Send message failed');
        })
    }

    function InitMap () {
      for(var k in displayPlans) {
        if(displayPlans[k].location) {
          CreateMarker(displayPlans[k].category.icon_url, displayPlans[k].title, displayPlans[k].id, displayPlans[k].location);
        } else if(displayPlans[k].points) {
          var points = displayPlans[k].points;

          for(var i in points) {
            CreateMarker(displayPlans[k].category.icon_url, displayPlans[k].title, displayPlans[k].id, points[i].location);
          }
        }
      }

      MapService.FitBoundsOfCurrentLayer();
    }

    function CreateMarker(iconUrl, title, plan_id, location) {

      var icon = MapService.CreateCustomIcon(iconUrl, 'planner-icon');
      var options = {};

      if (icon) options.icon = icon;
      if (title) options.title = title;

      MapService.CreateMarker(location, options);
    }

    InitMap();

  }
})();
