(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ProfileController', ProfileController);

  /** @ngInject */
  function ProfileController(user, wall, Wall, ScrollService) {
    var vm = this;
    vm.wall = wall;
    vm.pagination = new ScrollService();

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
          console.log(resp);
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
