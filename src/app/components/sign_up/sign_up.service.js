(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SignUpService', SignUpService);

  /** @ngInject */
  function SignUpService($modal, $rootScope, User, toastr) {
    return {
      openModal: openModal,
      signUpUser: signUpUser
    };

    function openModal(template, controller) {
      $modal.open({
        templateUrl: template,
        controller: controller,
        controllerAs: 'modal'
        //size: 'lg'
      });
    }

    function signUpUser(form, vm, $modalInstance) {
      if (form.$valid) {
        User.signUp({user: this},
          function success(user) {
            $rootScope.currentUser = user;
            $modalInstance.dismiss('close');
          }, function error(resp) {
            console.log(resp);
            //form.email.$setValidity('wrong', true);
            //form.inputName.$setValidity('required', false);
            toastr.error('Wrong email or password');
          });
      }
    }


  }

})();
