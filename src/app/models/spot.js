(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Spot', Spot);

  /** @ngInject */
  function Spot($resource, API_URL) {
    return $resource(API_URL + '/spots/:id', {id: '@id'}, {
      query: {
        isArray: false,
        params: {
          page: 1,
          limit: 10
        }
      },
      favorites: {
        url: API_URL + '/spots/favorites',
        params: {
          page: 1,
          limit: 20
        }
      },
      inviteFriends: {
        url: API_URL + '/spots/invite',
        method: 'POST'
      },
      saveToCalendar: {
        url: API_URL + '/calendar/:id',
        method: 'POST',
        ignoreLoadingBar: true
      },
      removeFromCalendar: {
        url: API_URL + '/calendar/:id',
        method: 'DELETE',
        ignoreLoadingBar: true
      },
      favorite: {
        url: API_URL + '/spots/:id/favorite',
        ignoreLoadingBar: true
      },
      unfavorite: {
        url: API_URL + '/spots/:id/unfavorite',
        ignoreLoadingBar: true
      },
      rate: {
        url: API_URL + '/spots/:id/rate',
        method: 'POST',
        ignoreLoadingBar: true
      }
    });
  }

})();
