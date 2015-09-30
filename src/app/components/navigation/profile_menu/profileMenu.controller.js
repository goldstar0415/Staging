(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ProfileMenuController', ProfileMenuController);

  /** @ngInject */
  function ProfileMenuController(User, $state, PermissionService, $rootScope, USER_ONLINE_MINUTE) {
    var vm = this;
    vm.$state = $state;
    vm.checkPermision = PermissionService.checkPermission;

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

    vm.isActive = function (state) {
      return {
        //active: $state.includes()
        active: state == $state.current.name || $state.current.name.indexOf(state + '.') >= 0
      };
    };

    $rootScope.isOnline = function (user) {
      var online = false;
      if (user.last_action_at) {
        var lastAction = moment(user.last_action_at);
        online = (lastAction.diff(moment(), 'minutes') + moment().utcOffset()) >= USER_ONLINE_MINUTE;
      }

      return {online: online, offline: !online};
    };

    $rootScope.isRole = function (user, name) {
      if (user) {
        var roles = _.pluck(user.roles, 'name');
        return roles.length > 0 && roles.indexOf(name) >= 0;
      }
    }
  }
})();
