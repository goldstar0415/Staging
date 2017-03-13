(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ZoomersController', ZoomersController);

  /** @ngInject */
  function ZoomersController(User, ScrollService, PermissionService) {
    var vm = this;
    vm.type = 'all';
    vm.users = {};
    vm.checkPermission = PermissionService.checkPermission;

    var params = {
      page: 0,
      limit: 10,
      type: 'all'
    };
    vm.pagination = new ScrollService(User.query, vm.users, params);

    vm.setType = function (type) {
      vm.type = type;
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
      vm.users.data = [];
      vm.pagination.params.page = 0;
      vm.pagination.params.type = vm.type;
      vm.pagination.params.filter = vm.filter;
      vm.pagination.nextPage();
    }

  }
})();
