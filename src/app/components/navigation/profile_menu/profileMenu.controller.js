(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ProfileMenuController', ProfileMenuController);

  /** @ngInject */
  function ProfileMenuController(User) {
    var vm = this;


    vm.follow = function (user) {
      if (user.can_follow) {
        User.follow({user_id: user.id});
      } else {
        User.unfollow({user_id: user.id});
      }
      user.can_follow = !user.can_follow;
    };
  }
})();
