(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('UserService', UserService);

  /** @ngInject */
  function UserService($rootScope, User, toastr) {
    return {
      getUserProfile: getUserProfile
    };

    function getUserProfile(id) {
      User.get({id: id}, function success(userProfile) {
        $rootScope.userProfile = userProfile;
      }, function error(resp) {
        $log.error(resp);
        toastr.error('Can\'t get user profile');
      });
    }
  }

})();
