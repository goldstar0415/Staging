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
          Friends.save(user, function () {
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
