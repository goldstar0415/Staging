(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('inviteFriends', inviteFriends);

  /** @ngInject */
  function inviteFriends() {
    return {
      restrict: 'E',
      templateUrl: 'app/components/invite_friends/invite_friends.html',
      scope: {
        spot: '='
      },
      controller: InviteFriendsController,
      controllerAs: 'InviteFriends',
      bindToController: true
    };

    /** @ngInject */
    function InviteFriendsController($modal, $rootScope) {
      var vm = this;

      vm.openModal = function () {
        $modal.open({
          templateUrl: 'InviteFriendsModal.html',
          controller: InviteFriendsModalController,
          controllerAs: 'modal',
          modalClass: 'authentication',
          resolve: {
            spot: function () {
              return vm.spot;
            },
            friends: function (User) {
              return User.followers({user_id: $rootScope.currentUser.id}).$promise;
            }
          }
        });
      };

    }

    function InviteFriendsModalController(spot, friends, $modalInstance, Spot) {
      var vm = this;
      vm.friends = friends;

      vm.close = function () {
        $modalInstance.close();
      };
      vm.inviteFriends = function () {
        var selectedUsers = _.where(vm.friends, {selected: true});
        if (selectedUsers.length > 0) {
          Spot.inviteFriends({
            spot_id: spot.id,
            users: _.pluck(selectedUsers, 'id')
          }, function success() {
            $modalInstance.close();
          })
        }
      };
    }

  }

})
();
