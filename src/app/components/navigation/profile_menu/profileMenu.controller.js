(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ProfileMenuController', ProfileMenuController);

  /** @ngInject */
  function ProfileMenuController(User, $state, PermissionService) {
    var vm = this;
    vm.$state = $state;
    vm.checkPermision = PermissionService.checkPermission;

    vm.follow = function (user) {
      if (user.can_follow) {
        User.follow({user_id: user.id});
      } else {
        User.unfollow({user_id: user.id});
      }
      user.can_follow = !user.can_follow;
    };

    vm.isActive = function (state) {
      return {
        //active: $state.includes()
        active: state == $state.current.name || $state.current.name.indexOf(state + '.') >= 0
      };
    }
  }
})();
