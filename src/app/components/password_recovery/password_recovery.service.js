(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('PasswordRecoveryService', PasswordRecoveryService);

  /** @ngInject */
  function PasswordRecoveryService($modal, UserService, User, toastr) {
    return {
      openModal: openModal,
      recoveryPassword: recoveryPassword
    };

    function openModal(template, controller) {
      $modal.open({
        templateUrl: template,
        controller: controller,
        controllerAs: 'modal',
        modalClass: 'authentication'
      });
    }

    function recoveryPassword(form, vm, $modalInstance) {
      if (form.$valid) {
        User.recoveryPassword({user: vm},
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


  }

})();
