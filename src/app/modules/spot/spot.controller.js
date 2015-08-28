(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotController', SpotController);

  /** @ngInject */
  function SpotController(spot, SpotService, ScrollService, SpotComment) {
    var vm = this;
    vm.spot = SpotService.formatSpot(spot);
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.removeSpot = SpotService.removeSpot;
    vm.postComment = postComment;

    vm.comments = {};
    var params = {
      page: 0,
      limit: 10,
      spot_id: spot.id
    };
    vm.pagination = new ScrollService(SpotComment.query, vm.comments, params);


    function postComment() {
      SpotComment.save({spot_id: spot.id},
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
