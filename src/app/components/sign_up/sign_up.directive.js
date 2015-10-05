(function () {
  'use strict';

  /*
   * Directive for sign up modal
   */
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

      //open sign up modal
      vm.openSignUpModal = function () {
        SignUpService.openModal('SignUpModal.html');
      };
    }

  }

})();
