(function () {
  'use strict';

  /*
   User service
   */
  angular
    .module('zoomtivity')
    .factory('UserService', UserService);

  /** @ngInject */
  function UserService($rootScope, $q, User, socket, $state, $http, $timeout, DATE_FORMAT, API_URL, toastr) {
    return {
      getCurrentUserPromise: getCurrentUserPromise,
      setCurrentUser: setCurrentUser,
      setProfileUser: setProfileUser,
      logOut: logOut,
      unSubscribe: unSubscribe
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

      if (!user.country || !user.ip) {
        sendIpLocation();
      }

      $rootScope.currentUserFailed = false;
      socket.connect(user.random_hash);

      $timeout(fixFacebookHashUrl);
    }

    /*
     * Set profile user
     * @param user {User}
     */
    function setProfileUser(user) {
      $rootScope.profileUser = user;

      if ($rootScope.currentUser && $rootScope.currentUser.id == user.id) {
        var now = moment().format(DATE_FORMAT.backend);
        user.last_action_at = now;
      }
    }

    //set current user location by IP
    function sendIpLocation() {
		return;
		// no another service is being used
      $http.jsonp("http://ipinfo.io?callback=JSON_CALLBACK").success(function (response) {
        User.setLocation({
          ip: response.ip,
          city: response.city,
          country: response.country
        });
      });
    }
    
    function unSubscribe(toastr) {
      $http.get(API_URL + '/unsubscribe')
          .success(function (data, status, headers, config) {
            toastr.success('You successfully unsubscribed');
            $rootScope.currentUser.notification_letter = false;
            $rootScope.currentUser.notification_wall_post = false;
            $rootScope.currentUser.notification_follow = false;
            $rootScope.currentUser.notification_new_spot = false;
            $rootScope.currentUser.notification_coming_spot = false;
          })
          .error(function (data, status, headers, config) {
            toastr.error('You are not authorized')
          });
    }

    function fixFacebookHashUrl() {
      if (window.location.hash && window.location.hash === "#_=_") {
        if (window.history && history.pushState) {
          window.history.pushState("", document.title, window.location.pathname);
        } else {
          var scroll = {
            top: document.body.scrollTop,
            left: document.body.scrollLeft
          };
          window.location.hash = "";
          document.body.scrollTop = scroll.top;
          document.body.scrollLeft = scroll.left;
        }
      }
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
