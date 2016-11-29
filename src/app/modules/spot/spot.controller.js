(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotController', SpotController);

  /** @ngInject */
  function SpotController(spot, SpotService, ScrollService, SpotReview, SpotComment, $state, MapService, $rootScope, dialogs, API_URL, InviteFriends, Share) {

    console.log('Spot Init');

    var vm = this;
    vm.API_URL = API_URL;
    vm.spot = SpotService.formatSpot(spot);
    vm.spot.rating = vm.spot.reviews_total.total.rating;
    vm.spot.photos = _.union(vm.spot.photos, vm.spot.comments_photos);
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.removeSpot = removeSpot;
    vm.setImage = setImage;
    vm.invite = openInviteModal;
    vm.share = openShareModal;

    function openInviteModal(item) {
        InviteFriends.openModal(item);
    }

    function openShareModal(item, type) {
        Share.openModal(item, type);
    }

    vm.postComment = postComment;
    vm.deleteComment = deleteComment;

    $rootScope.syncSpots = {data: [vm.spot]};
    $rootScope.currentSpot = vm.spot;

    vm.votes = {};

    vm.comments = {};
    var params = {
      page: 0,
      limit: 10,
      spot_id: spot.id
    };
    vm.pagination = new ScrollService(SpotComment.query, vm.comments, params);
    vm.reviewsPagination = new ScrollService(SpotReview.query, vm.votes, params);
    ShowMarkers([vm.spot]);

    function setImage() {
        if (vm.spot.category.type.name === 'food') {
            if (false) {
                return vm.spot.cover_url.original;
            } else {
                var imgnum = Math.floor(vm.spot.id % 33);
                return '../../../assets/img/placeholders/food/' + imgnum + '.jpg';
            }
        } else {
            return vm.spot.cover_url.original;
        }
    }

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
