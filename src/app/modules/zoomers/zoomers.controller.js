(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ZoomersController', ZoomersController);

  /** @ngInject */
  function ZoomersController(User, users) {
    var vm = this;
    vm.type = 'all';
    vm.limit = 10;
    vm.page = 1;
    vm.users = users;

    vm.setType = function (type) {
      vm.type = type;
      vm.limit = 10;
      vm.page = 1;
      vm.getUsers();
    };

    vm.follow = function (user) {
      if (user.can_follow) {
        User.follow({user_id: user.id});
      } else {
        User.unfollow({user_id: user.id});
      }
      user.can_follow = !user.can_follow;
    };

    vm.getUsers = function () {
      User.query({
        type: vm.type,
        page: vm.page,
        limit: vm.limit,
        filter: vm.filter
      }, function (users) {
        vm.users = users;
      })
    }

  }
})();
