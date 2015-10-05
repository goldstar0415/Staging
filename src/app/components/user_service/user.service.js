(function () {
  'use strict';

  /*
   User service
   */
  angular
    .module('zoomtivity')
    .factory('UserService', UserService);

  /** @ngInject */
  function UserService($rootScope, $q, User, socket, $state) {
    return {
      getCurrentUserPromise: getCurrentUserPromise,
      setCurrentUser: setCurrentUser,
      setProfileUser: setProfileUser,
      logOut: logOut
    };

    function getCurrentUserPromise() {
      var deferred = $q.defer();

      User.currentUser({}, function success(user) {
        setCurrentUser(user);
        deferred.resolve(user);
      }, function fail() {
        $rootScope.currentUserFailed = true;
        deferred.resolve();
      });

      return deferred.promise;
    }

    /*
     * Set current user
     * @param user {User}
     */
    function setCurrentUser(user) {
      $rootScope.currentUser = user;

      if (!$rootScope.profileUser) {
        setProfileUser(user);
      }

      $rootScope.currentUserFailed = false;
      socket.connect(user.random_hash);
    }

    /*
     * Set profile user
     * @param user {User}
     */
    function setProfileUser(user) {
      $rootScope.profileUser = user;
    }

    /*
     * Logout user
     */
    function logOut() {
      $rootScope.currentUser = null;
      socket.disconnect();

      if ($state.current.require_auth) {
        $state.go('index');
      }
    }
  }

})();
