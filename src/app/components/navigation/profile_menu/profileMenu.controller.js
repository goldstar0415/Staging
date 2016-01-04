(function () {
  'use strict';

  /*
   * Controller for profile menu
   */
  angular
    .module('zoomtivity')
    .controller('ProfileMenuController', ProfileMenuController);

  /** @ngInject */
  function ProfileMenuController(User, $state, PermissionService) {
    var vm = this;
    vm.$state = $state;
    vm.checkPermission = PermissionService.checkPermission;

    //follow user and reload page
    vm.follow = function (user) {
      if (user.can_follow) {
        User.follow({user_id: user.id}, reloadPage);
      } else {
        User.unfollow({user_id: user.id}, reloadPage);
      }
      user.can_follow = !user.can_follow;
    };

    function reloadPage() {
      $state.go($state.current, {}, {reload: true});
    }

    //check is page active
    vm.isActive = function (state) {
      return {
        //active: $state.includes()
        active: state == $state.current.name || $state.current.name.indexOf(state + '.') >= 0
      };
    };


  }
})();
