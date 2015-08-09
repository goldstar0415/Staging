(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('UserService', UserService);

  /** @ngInject */
  function UserService($rootScope, socket, $state) {
    return {
      setCurrentUser: setCurrentUser,
      logOut: logOut
    };

    function setCurrentUser(user) {
      $rootScope.currentUser = user;

      if (!$rootScope.profileUser) {
        $rootScope.profileUser = user;
      }

      $rootScope.currentUserFailed = false;
      socket.connect(user.random_hash);
    }

    function logOut() {
      $rootScope.currentUser = null;
      socket.disconnect();

      if ($state.current.require_auth) {
        $state.go('index');
      }
    }
  }

})();
