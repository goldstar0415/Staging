(function () {
  'use strict';

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
      console.log($state);
      User.currentUser({}, function success(user) {
        setCurrentUser(user);
        if ($state.current.parent != 'profile') {
          setProfileUser(user);
        }
        deferred.resolve(user);
      }, function fail() {
        $rootScope.currentUserFailed = true;
        deferred.resolve();
      });

      return deferred.promise;
    }

    function setCurrentUser(user) {
      $rootScope.currentUser = user;

      if (!$rootScope.profileUser) {
        setProfileUser(user);
      }

      $rootScope.currentUserFailed = false;
      socket.connect(user.random_hash);
    }

    function setProfileUser(user) {
      $rootScope.profileUser = user;
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
