(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('signUp', signUp);

  /** @ngInject */
  function signUp() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/sign_up/sign_up.html',
      controller: SignUpController,
      controllerAs: 'signUp',
      bindToController: true
    };

    /** @ngInject */
    function SignUpController(SignUpService) {
      var vm = this;

      vm.openSignUpModal = function () {
        SignUpService.openModal('SignUpModal.html', SignUpModalController);
      };
    }

    /** @ngInject */
    function SignUpModalController(SignUpService, $modalInstance) {
      var vm = this;

      vm.close = function () {
        $modalInstance.close();
      };
      vm.signUpUser = function (form) {
        SignUpService.signUpUser(form, vm, $modalInstance);
      };
    }

  }

})();
