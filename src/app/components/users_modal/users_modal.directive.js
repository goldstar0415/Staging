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
              return _.include(s.type) ? s.type : 'followings';
            },
            user: function () {
              return s.user;
            },
            users: function (User, Spot) {
              switch (s.type) {
                case 'followers':
                  return User.followers({user_id: s.user.id}).$promise;
                case 'followings':
                  return User.followings({user_id: s.user.id}).$promise;
                case 'members':
                  return Spot.members({id: s.user.id}).$promise;
              }
            }
          }
        });
      });

    }

    /** @ngInject */
    function FollowersModalController(usersType, user, users, $modalInstance) {
      var vm = this;
      vm.usersType = usersType == 'followings' ? 'following' : usersType;
      vm.user = user;
      vm.users = users;

      vm.close = function () {
        $modalInstance.close();
      };
    }
  }
})
();
