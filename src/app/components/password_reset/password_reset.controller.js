(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ResetPasswordController', ResetPasswordController);

  /** @ngInject */
  function ResetPasswordController(PasswordRecoveryService) {
    PasswordRecoveryService.openModal('ResetPasswordModal.html', ResetPasswordModalController);

  }

  function ResetPasswordModalController(PasswordRecoveryService, $stateParams, $modalInstance) {
    var vm = this;
    vm.token = $stateParams.token;

    vm.close = function () {
      $modalInstance.close();
    };
    vm.resetPassword = function (form) {
      PasswordRecoveryService.resetPassword(form, vm, $modalInstance);
    };
  }

})();
