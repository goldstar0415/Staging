(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ResetPasswordController', ResetPasswordController);

  /** @ngInject */
  function ResetPasswordController(PasswordRecoveryService, $rootScope) {
    if (!$rootScope.currentUser) {
      PasswordRecoveryService.openModal('ResetPasswordModal.html', ResetPasswordModalController);
    }
  }

  /** @ngInject */
  function ResetPasswordModalController(PasswordRecoveryService, $stateParams, $modalInstance) {
    var vm = this;
    vm.token = $stateParams.token;

    //close modal
    vm.close = function () {
      $modalInstance.close();
    };

    //send reset password form
    vm.resetPassword = function (form) {
      PasswordRecoveryService.resetPassword(form, vm, $modalInstance);
    };
  }

})();
