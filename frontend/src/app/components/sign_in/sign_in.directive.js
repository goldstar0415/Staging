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
      template: '<a ng-click="signIn.openSignInModal()">{{signIn.title}}</a>',
      scope: {
        title: '@'
      },
      controller: SignInController,
      controllerAs: 'signIn',
      bindToController: true
    };

    /** @ngInject */
    function SignInController(SignInService) {
      var vm = this;

      vm.openSignInModal = SignInService.openModal;
    }
  }

})();
