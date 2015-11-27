(function () {
  'use strict';

  /*
   * User model
   */
  angular
    .module('zoomtivity')
    .factory('User', User);

  /** @ngInject */
  function User($resource, API_URL) {
    return $resource(API_URL + '/users/:id', {id: '@id'}, {
      query: {
        url: API_URL + '/users/list',
        method: 'GET',
        params: {
          type: 'all',
          page: 1,
          limit: 10
        }
      },
      currentUser: {
        url: API_URL + '/users/me'
      },
      signIn: {
        url: API_URL + '/users/login',
        method: 'POST'
      },
      signUp: {
        url: API_URL + '/users',
        method: 'POST'
      },
      logOut: {
        url: API_URL + '/users/logout',
        method: 'GET'
      },
      recoveryPassword: {
        url: API_URL + '/users/recovery',
        method: 'POST'
      },
      resetPassword: {
        url: API_URL + '/users/reset',
        method: 'POST'
      },
      follow: {
        url: API_URL + '/follow/:user_id',
        method: 'GET'
      },
      unfollow: {
        url: API_URL + '/unfollow/:user_id',
        method: 'GET'
      },
      followers: {
        url: API_URL + '/followers/:user_id',
        isArray: true
      },
      followings: {
        url: API_URL + '/followings/:user_id',
        isArray: true
      },
      update: {
        method: 'PUT'
      },
      setAvatar: {
        url: API_URL + '/settings/setavatar',
        method: 'POST'
      },
      checkAlias: {
        url: API_URL + '/settings/alias',
        method: 'GET'
      },
      setLocation: {
        url: API_URL + '/settings/location',
        method: 'POST'
      }
    });
  }
})();
