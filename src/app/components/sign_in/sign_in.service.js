(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SignInService', SignInService);

  /** @ngInject */
  function SignInService(UserService, User, toastr, $state, $modal) {
    return {
      userLogin: userLogin,
      openModal: openModal
    };

    function openModal() {
      $modal.open({
        templateUrl: '/app/components/sign_in/sign_in.html',
        controller: SignInModalController,
        controllerAs: 'modal',
        modalClass: 'authentication'
      });
    }

    /*
     * Send login form
     * @param form {ngForm}
     * @param user {User}
     * @param $modalInstance {Object}
     */
    function userLogin(form, user, $modalInstance) {
      if (form.$valid) {
        User.signIn(user,
          function success(user) {
            UserService.setCurrentUser(user);
            $modalInstance.dismiss('close');
          }, function error(resp) {
            if (resp.status == 400) {
              $state.go($state.current, {}, {reload: true});
            } else {
              toastr.error('Wrong email or password');
            }
          });
      }
    }
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
      SignInService.userLogin(form, {email: vm.email, password: vm.password}, $modalInstance);
    };
  }

})();
