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

      vm.openSignInModal = SignInService.openModal(SignInController);
      vm.userLogin = SignInService.userLogin;


    }
  }

})();
