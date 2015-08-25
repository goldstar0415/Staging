(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('usersModal', UsersModal);

  /** @ngInject */
  function UsersModal($modal) {
    return {
      restrict: 'A',
      scope: {
        user: '=',
        type: '@usersModal'
      },
      link: UsersModalLink
    };

    /** @ngInject */
    function UsersModalLink(s, e, a) {
      e.click(function () {
        $modal.open({
          templateUrl: 'FollowersModal.html',
          controller: FollowersModalController,
          controllerAs: 'modal',
          modalClass: 'authentication',
          resolve: {
            usersType: function () {
              return s.type;
            },
            user: function () {
              return s.user;
            },
            users: function (User) {
              return s.type == 'followers' ?
                User.followers({user_id: s.user.id}).$promise :
                User.followings({user_id: s.user.id}).$promise;
            }
          }
        });
      });

    }

    /** @ngInject */
    function FollowersModalController(usersType, user, users, $modalInstance) {
      var vm = this;
      vm.usersType = usersType == 'followers' ? 'followers' : 'followings';
      vm.user = user;
      vm.users = users;
    }
  }
})
();
