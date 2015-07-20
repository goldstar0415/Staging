(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SignUpModalController', SignUpModalController);

  /** @ngInject */
  function SignUpModalController(SignUpService, $modalInstance) {
    var vm = this;

    vm.signUpUser = function (form) {
      SignUpService.signUpUser(form, vm, $modalInstance);
    };
  }

})();
