(function () {
  'use strict';

  /*
   * Password Recovery Modal
   */
  angular
    .module('zoomtivity')
    .directive('passwordRecovery', passwordRecovery);

  /** @ngInject */
  function passwordRecovery() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/password_recovery/password_recovery.html',
      controller: PasswordRecoveryController,
      controllerAs: 'password',
      bindToController: true
    };

    /** @ngInject */
    function PasswordRecoveryController(PasswordRecoveryService) {
      var vm = this;

      //Open password recovery modal
      vm.openPasswordRecoveryModal = function () {
        PasswordRecoveryService.openModal('PasswordRecoveryModal.html', PasswordRecoveryModalController);
      };
    }

    /** @ngInject */
    function PasswordRecoveryModalController(PasswordRecoveryService, $modalInstance) {
      var vm = this;

      //close modal
      vm.close = function () {
        $modalInstance.close();
      };

      //send recovery password form
      vm.recoveryPassword = function (form) {
        PasswordRecoveryService.recoveryPassword(form, vm, $modalInstance);
      };
    }

  }

})();
