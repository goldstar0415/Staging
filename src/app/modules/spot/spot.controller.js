(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotController', SpotController);

  /** @ngInject */
  function SpotController(spot, SpotService, ScrollService, SpotComment, $state, MapService, $rootScope, dialogs, API_URL) {
    var vm = this;
    vm.API_URL = API_URL;
    vm.spot = SpotService.formatSpot(spot);
    $rootScope.currentSpot = vm.spot;
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.removeSpot = removeSpot;

    vm.postComment = postComment;
    vm.deleteComment = deleteComment;

    vm.comments = {};
    var params = {
      page: 0,
      limit: 10,
      spot_id: spot.id
    };
    vm.pagination = new ScrollService(SpotComment.query, vm.comments, params);

    ShowMarkers([vm.spot]);

    function removeSpot(spot, idx) {
      SpotService.removeSpot(spot, idx, function () {
        $state.go('spots', {user_id: $rootScope.currentUser.id});
      });
    }

    function postComment() {
      SpotComment.save({spot_id: spot.id},
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
          console.warn(resp);
          toastr.error('Send message failed');
        })
    }

    function ShowMarkers(spots) {
      var spotsArray = _.map(spots, function (item) {
        return {
          id: item.id,
          spot_id: item.spot_id,
          locations: item.points,
          address: '',
          spot: item
        };
      });
      MapService.drawSpotMarkers(spotsArray, 'other', true);
      MapService.FitBoundsOfCurrentLayer();
    }

    function deleteComment(comment, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete comment?').result.then(function () {
        SpotComment.delete({spot_id: spot.id, id: comment.id}, function () {
          toastr.info('Comment successfully deleted');
          vm.comments.data.splice(idx, 1);
        });
      });
    }
  }
})();
