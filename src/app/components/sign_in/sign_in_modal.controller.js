(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SignInModalController', SignInModalController);

  /** @ngInject */

  function SignInModalController(SignInService, $modalInstance) {
    var vm = this;

    vm.userLogin = function (form) {
      SignInService.userLogin(form, vm, $modalInstance);
    };
  }

})();
