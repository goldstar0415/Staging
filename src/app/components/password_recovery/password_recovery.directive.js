(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('passwordRecovery', passwordRecovery);

  /** @ngInject */
  function passwordRecovery() {
    return {
      restrict: 'E',
      templateUrl: 'app/components/password_recovery/password_recovery.html',
      controller: PasswordRecoveryController,
      controllerAs: 'password',
      bindToController: true
    };

    /** @ngInject */
    function PasswordRecoveryController(PasswordRecoveryService) {
      var vm = this;

      vm.openPasswordRecoveryModal = function () {
        PasswordRecoveryService.openModal('PasswordRecoveryModal.html', PasswordRecoveryModalController);
      };
    }

    function PasswordRecoveryModalController(PasswordRecoveryService, $modalInstance) {
      var vm = this;

      vm.recoveryPassword = function (form) {
        PasswordRecoveryService.recoveryPassword(form, vm, $modalInstance);
      };
    }

  }

})();
