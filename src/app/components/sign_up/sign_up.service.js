(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SignUpService', SignUpService);

  /** @ngInject */
  function SignUpService($modal, UserService, User, toastr, $state) {
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

    /*
     * Send sign up form
     * @param form {ngForm}
     * @param user {User}
     * @param $modalInstance {Object}
     */
    function signUpUser(form, user, $modalInstance) {
      if (form.$valid) {
        User.signUp(user,
          function success(user) {
            UserService.setCurrentUser(user);

            $modalInstance.dismiss('close');
          }, function error(resp) {
            if (resp.status == 400) {
              $state.go($state.current, {}, {reload: true});
            } else {
              var message = resp.data.email || resp.data.message || 'Wrong data';
              toastr.error(message);
            }
          });
      }
    }

    /** @ngInject */
    function SignUpModalController(SignUpService, $modalInstance, API_URL) {
      var vm = this;
      vm.API_URL = API_URL;

      //close modal
      vm.close = function () {
        $modalInstance.close();
      };

      //send form
      vm.signUpUser = function (form) {
        SignUpService.signUpUser(form, vm, $modalInstance);
      };
    }
  }

})();
