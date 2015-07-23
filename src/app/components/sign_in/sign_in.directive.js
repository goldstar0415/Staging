(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('signIn', signIn);

  /** @ngInject */
  function signIn() {
    return {
      restrict: 'E',
      templateUrl: 'app/components/sign_in/sign_in.html',
      controller: SignInController,
      controllerAs: 'signIn',
      bindToController: true
    };

    /** @ngInject */
    function SignInController(SignInService) {
      var vm = this;

      vm.openSignInModal = function () {
        SignInService.openModal('SignInModal.html', SignInModalController);
      };

      vm.userLogin = SignInService.userLogin;
    }

    function SignInModalController(SignInService, $modalInstance) {
      var vm = this;

      vm.close = function () {
        $modalInstance.close();
      };
      vm.userLogin = function (form) {
        SignInService.userLogin(form, vm, $modalInstance);
      };
    }

  }

})();
