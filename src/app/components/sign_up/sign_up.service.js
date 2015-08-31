(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SignUpService', SignUpService);

  /** @ngInject */
  function SignUpService($modal, UserService, User, toastr) {
    return {
      openModal: openModal,
      signUpUser: signUpUser
    };

    function openModal(template) {
      $modal.open({
        templateUrl: template,
        controller: SignUpModalController,
        controllerAs: 'modal',
        modalClass: 'authentication'
      });
    }

    function signUpUser(form, user, $modalInstance) {
      if (form.$valid) {
        User.signUp(user,
          function success(user) {
            UserService.setCurrentUser(user);

            $modalInstance.dismiss('close');
          }, function error(resp) {
            console.log(resp);
            //form.email.$setValidity('wrong', true);
            //form.inputName.$setValidity('required', false);
            toastr.error('Wrong email or password');
          });
      }
    }

    /** @ngInject */
    function SignUpModalController(SignUpService, $modalInstance, API_URL) {
      var vm = this;
      vm.API_URL = API_URL;

      vm.close = function () {
        $modalInstance.close();
      };
      vm.signUpUser = function (form) {
        SignUpService.signUpUser(form, vm, $modalInstance);
      };
    }
  }

})();
