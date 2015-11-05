(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('GoogleContactsController', GoogleContactsController);

  /** @ngInject */
  function GoogleContactsController(contacts, $modalInstance, API_URL, Friends) {
    var vm = this;
    vm.API_URL = API_URL;
    vm.users = contacts;

    vm.save = function () {
      $modalInstance.close();
      _.each(vm.users, function (user) {
        if (user.selected) {
          var photo = user.photo;
          delete user.photo;
          Friends.save(user, function (friend) {
            if (photo) {
              Friends.setAvatar({id: friend.id}, {avatar: photo});
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
