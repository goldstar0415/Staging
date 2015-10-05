(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('PasswordRecoveryService', PasswordRecoveryService);

  /** @ngInject */
  function PasswordRecoveryService($modal, UserService, User, toastr) {
    return {
      openModal: openModal,
      recoveryPassword: recoveryPassword,
      resetPassword: resetPassword
    };

    function openModal(template, controller) {
      $modal.open({
        templateUrl: template,
        controller: controller,
        controllerAs: 'modal',
        modalClass: 'authentication'
      });
    }

    /*
     * Send password recovery form
     * @param form {ngForm}
     * @param vm {Object} input data
     * @param $modalInstance {Object}
     */
    function recoveryPassword(form, vm, $modalInstance) {
      if (form.$valid) {
        User.recoveryPassword(vm,
          function success(user) {
            UserService.setCurrentUser(user);
            $modalInstance.dismiss('close');
          }, function error(resp) {
            toastr.error('Wrong email');
          });
      }
    }

    /*
     * Send reset password form
     * @param form {ngForm}
     * @param vm {Object} input data
     * @param $modalInstance {Object}
     */
    function resetPassword(form, vm, $modalInstance) {
      if (form.$valid) {
        User.resetPassword(vm,
          function success(user) {
            toastr.success('Password successfully changed');
            $modalInstance.dismiss('close');
          }, function error(resp) {
            toastr.error('Wrong email');
          });
      }
    }


  }

})();
