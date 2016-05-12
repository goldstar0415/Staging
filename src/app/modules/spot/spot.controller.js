(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotController', SpotController);

  /** @ngInject */
  function SpotController(spot, SpotService, ScrollService, SpotComment, $state, MapService, $rootScope, dialogs, API_URL) {

    console.log('Spot Init');

    var vm = this;
    vm.API_URL = API_URL;
    vm.spot = SpotService.formatSpot(spot);
    vm.spot.photos = _.union(vm.spot.photos, vm.spot.comments_photos);
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.removeSpot = removeSpot;

    vm.postComment = postComment;
    vm.deleteComment = deleteComment;

    $rootScope.syncSpots = {data: [vm.spot]};
    $rootScope.currentSpot = vm.spot;

    vm.comments = {};
    var params = {
      page: 0,
      limit: 10,
      spot_id: spot.id
    };
    vm.pagination = new ScrollService(SpotComment.query, vm.comments, params);

    ShowMarkers([vm.spot]);

    /*
     * Delete spot
     * @param spot {Spot}
     * @param idx {number} spot index
     */
    function removeSpot(spot, idx) {
      SpotService.removeSpot(spot, idx, function () {
        $state.go('spots', {user_id: $rootScope.currentUser.id});
      });
    }

    //send new comment for spot
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

    //show markers on map
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

    /*
     * Delete comment
     * @param comment {SpotComment}
     * @param idx {number} comment index
     */
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
