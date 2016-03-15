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
    vm.savePlanToCalendar = savePlanToCalendar;
    vm.removePlanFromCalendar = removePlanFromCalendar;
    vm.send = send;
    formatAttachments();

    var displayPlans = _.union(plan.activities, plan.spots);

    var params = {
      page: 0,
      limit: 10,
      plan_id: plan.id
    };
    vm.pagination = new ScrollService(PlanComment.query, vm.comments, params);


    //submit form
    function send() {
      PlanComment.save({plan_id: plan.id},
        {
          body: vm.message || '',
          attachments: {
            album_photos: _.pluck(vm.attachments.photos, 'id'),
            spots: _.pluck(vm.attachments.spots, 'id'),
            areas: _.pluck(vm.attachments.areas, 'id'),
            links: vm.attachments.links
          }
        }, function success(message) {
          vm.comments.data.unshift(message);
          vm.message = '';
          vm.attachments.photos = [];
          vm.attachments.spots = [];
          vm.attachments.areas = [];
          vm.attachments.links = [];
        }, function error(resp) {
          toastr.error('Send message failed');
        })
    }

    function savePlanToCalendar() {

    }

    function removePlanFromCalendar() {

    }

    //show plans on map
    function InitMap() {
      for (var k in displayPlans) {
        if (displayPlans[k].location) {
          CreateMarker(displayPlans[k].category.icon_url, displayPlans[k].title, displayPlans[k].id, displayPlans[k].location);
        } else if (displayPlans[k].points) {
          var points = displayPlans[k].points;

          for (var i in points) {
            CreateMarker(displayPlans[k].category.icon_url, displayPlans[k].title, displayPlans[k].id, points[i].location);
          }
        }
      }
    }

    //create marker on map
    function CreateMarker(iconUrl, title, plan_id, location) {
      var icon = MapService.CreateCustomIcon(iconUrl, 'planner-icon');
      var options = {};

      if (icon) options.icon = icon;
      if (title) options.title = title;

      MapService.CreateMarker(location, options);
    }

    function formatAttachments() {
      _.each(vm.spots, function (spot) {
        spot.position = spot.pivot.position;
        spot.attachment_type = 'spot';
      });
      _.each(vm.activities, function (activity) {
        activity.attachment_type = 'activity';
      });
      vm.plan_attachments = _.union(vm.activities, vm.spots);
    }

    InitMap();
  }
})();
