(function () {
  'use strict';

  /*
   * Modals for invite friend to an event or plan
   */
  angular
    .module('zoomtivity')
    .directive('inviteFriends', inviteFriends);

  /** @ngInject */
  function inviteFriends() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/invite_friends/invite_friends.html',
      scope: {
        item: '=',
        type: '@'
      },
      controller: InviteFriendsController,
      controllerAs: 'InviteFriends',
      bindToController: true
    };

    /** @ngInject */
    function InviteFriendsController($modal, $rootScope, SignUpService) {
      var vm = this;

      vm.openModal = function () {
        if (!$rootScope.currentUser) {
          SignUpService.openModal('SignUpModal.html');
          return;
        }

        $modal.open({
          templateUrl: 'InviteFriendsModal.html',
          controller: InviteFriendsModalController,
          controllerAs: 'modal',
          modalClass: 'authentication',
          resolve: {
            type: function () {
              return vm.type;
            },
            item: function () {
              return vm.item;
            },
            friends: function (User) {
              return User.followers({user_id: $rootScope.currentUser.id}).$promise;
            }
          }
        });
      };

    }

    /** @ngInject */
    function InviteFriendsModalController(type, item, friends, $modalInstance, Spot, Plan) {
      var vm = this;
      vm.friends = filterOwner(friends);
      type = type || 'spot';

      vm.close = function () {
        $modalInstance.close();
      };

      //send invite to selected users
      vm.inviteFriends = function () {
        var selectedUsers = _.where(vm.friends, {selected: true});
        if (selectedUsers.length > 0) {
          var userIds = _.pluck(selectedUsers, 'id');
          if (type == 'spot') {
            Spot.inviteFriends({
              spot_id: item.id,
              users: userIds
            }, function success() {
              $modalInstance.close();
            });
          } else {
            Plan.inviteFriends({
              plan_id: item.id,
              users: userIds
            }, function success() {
              $modalInstance.close();
            });
          }
        }
      };

      vm.isAnySelected = function () {
        return _.findWhere(vm.friends, {selected: true});
      };

      function filterOwner(friends) {
        return _.filter(friends, function (user) {
          return user.id != item.user_id;
        });
      }
    }
  }

})
();
