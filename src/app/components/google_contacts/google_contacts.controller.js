(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('GoogleContactsController', GoogleContactsController);

  /** @ngInject */
  function GoogleContactsController(contacts, friends, $modalInstance, API_URL, Friends) {
    var vm = this;
    vm.API_URL = API_URL;
    vm.users = contacts;

    vm.save = function () {
      $modalInstance.close();
      _.each(vm.users, function (user) {
        if (user.selected) {
          var photo = user.photo;
          Friends.save({
            first_name: user.first_name,
            last_name: user.last_name,
            email: user.email,
            phone: user.phone
          }, function (friend) {
            if (photo) {
              Friends.setAvatar({id: friend.id}, {avatar: photo}, function (friendPhoto) {
                friends.push(friendPhoto);
              });
            } else {
              friends.push(friend);
            }

            toastr.success(user.first_name + ' successfully imported')
          }, function () {
            toastr.error(user.first_name + ' import failed')
          });
        }
      });
    };

    //close modal
    vm.close = function () {
      $modalInstance.close();
    };
  }
})();
