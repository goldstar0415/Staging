(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PlanController', PlanController);

  /** @ngInject */
  function PlanController(plan, comments, PlanComment, SpotService) {
    var vm = this;
    vm = _.extend(vm, plan);
    vm.comments = comments;
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.send = send;

    function send() {
      PlanComment.save({plan_id: plan.id},
        {
          message: vm.message || '',
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

  }
})();
