(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ZoomersController', ZoomersController);

  /** @ngInject */
  function ZoomersController(User, users, PermissionService) {
    var vm = this;
    vm.type = 'all';
    vm.limit = 10;
    vm.page = 1;
    vm.users = users;
    vm.checkPermision = PermissionService.checkPermission;

    vm.setType = function (type) {
      vm.type = type;
      vm.limit = 10;
      vm.page = 1;
      vm.getUsers();
    };

    /*
     * Follow and unfollow user
     * @param user {User}
     */
    vm.follow = function (user) {
      if (user.can_follow) {
        User.follow({user_id: user.id});
      } else {
        User.unfollow({user_id: user.id});
      }
      user.can_follow = !user.can_follow;
    };

    /*
     *  Load users
     */
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
