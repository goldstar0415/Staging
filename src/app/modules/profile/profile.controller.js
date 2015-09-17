(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ProfileController', ProfileController);

  /** @ngInject */
  function ProfileController(user, Wall, spots, SpotService, MapService, ScrollService, PermissionService) {
    var vm = this;
    vm.checkPermision = PermissionService.checkPermission;

    vm.wall = {};
    var params = {
      page: 0,
      limit: 10,
      user_id: user.id
    };
    vm.pagination = new ScrollService(Wall.query, vm.wall, params);

    if (spots) {
      var formatedSpots = formatSpots(spots);
      ShowMarkers(formatedSpots);
    }

    function formatSpots(spots) {
      return _.each(spots, function (spot) {
        SpotService.formatSpot(spot);
      });
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
      if (spots.length > 0 && spots[0].points.length > 0) {
        MapService.FocusMapToGivenLocation(spots[0].points[0].location, 12);
      }

    }

    vm.send = function () {
      Wall.save({
          user_id: user.id,
          message: vm.message || '',
          attachments: {
            album_photos: _.pluck(vm.attachments.photos, 'id'),
            spots: _.pluck(vm.attachments.spots, 'id'),
            areas: _.pluck(vm.attachments.areas, 'id')
          }
        }, function success(message) {
          vm.wall.data.unshift(message);
          vm.message = '';
          vm.attachments.photos = [];
          vm.attachments.spots = [];
          vm.attachments.areas = [];
        },
        function error(resp) {
          toastr.error('Send message failed');
        })
    };

    vm.like = function (post) {
      if (post.user_rating < 1) {
        Wall.like({id: post.id});
        post.user_rating++;
        post.rating++;
      }
    };

    vm.dislike = function (post) {
      if (post.user_rating > -1) {
        Wall.dislike({id: post.id});
        post.user_rating--;
        post.rating--;
      }
    };
  }
})();
