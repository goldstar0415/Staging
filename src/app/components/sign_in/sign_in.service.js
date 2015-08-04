(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SignInService', SignInService);

  /** @ngInject */
  function SignInService(UserService, User, toastr) {
    return {
      userLogin: userLogin
    };

    function userLogin(form, user, $modalInstance) {
      if (form.$valid) {
        User.signIn(user,
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
