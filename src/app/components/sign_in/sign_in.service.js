(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SignInService', SignInService);

  /** @ngInject */
  function SignInService(UserService, User, toastr, $state) {
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
            if (resp.status == 400) {
              $state.go($state.current, {}, {reload: true});
            } else {
              toastr.error('Wrong email or password');
            }
          });
      }
    }


  }

})();
