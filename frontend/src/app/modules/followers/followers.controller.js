(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FollowersController', FollowersController);

  /** @ngInject */
  function FollowersController(users, $state) {
    var vm = this;

    vm.isFollowings = $state.current.name == 'followings';

    vm.users = users;
  }
})();
