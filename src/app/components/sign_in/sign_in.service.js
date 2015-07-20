(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SignInService', SignInService);

  /** @ngInject */
  function SignInService($modal, $rootScope, User, toastr) {
    return {
      openModal: openModal,
      userLogin: userLogin
    };

    function openModal(template, controller) {
      $modal.open({
        templateUrl: template,
        controller: controller,
        controllerAs: 'modal',
        modalClass: 'authentication'
      });
    }

    function userLogin(form, vm, $modalInstance) {
      if (form.$valid) {
        User.signIn({user: this},
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
