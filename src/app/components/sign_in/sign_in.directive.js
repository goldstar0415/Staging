(function () {
  'use strict';

  /*
   * Directive for sign in modal
   */
  angular
    .module('zoomtivity')
    .directive('signIn', signIn);

  /** @ngInject */
  function signIn() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/sign_in/sign_in.html',
      scope: {
        title: '@'
      },
      controller: SignInController,
      controllerAs: 'signIn',
      bindToController: true
    };

    /** @ngInject */
    function SignInController($modal) {
      var vm = this;

      vm.openSignInModal = function () {
        $modal.open({
          templateUrl: 'SignInModal.html',
          controller: SignInModalController,
          controllerAs: 'modal',
          modalClass: 'authentication'
        });
      };

    }

    /** @ngInject */
    function SignInModalController(SignInService, API_URL, BACKEND_URL, $modalInstance) {
      var vm = this;
      vm.API_URL = API_URL;
      vm.BACKEND_URL = BACKEND_URL;

      //close modal
      vm.close = function () {
        $modalInstance.close();
      };

      //send login form
      vm.userLogin = function (form) {
        SignInService.userLogin(form, vm, $modalInstance);
      };
    }

  }

})();
