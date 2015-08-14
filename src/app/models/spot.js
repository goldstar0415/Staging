(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Spot', Spot);

  /** @ngInject */
  function Spot($resource, API_URL) {
    return $resource(API_URL + '/spots/:id', {id: '@id'}, {
      query: {
        isArray: false
      },
      favorites: {
        url: API_URL + '/spots/favorites',
        isArray: true
      },
      inviteFriends: {
        url: API_URL + '/spots/invite',
        method: 'POST'
      },
      saveToCalendar: {
        url: API_URL + '/calendar/:id',
        method: 'POST'
      },
      removeFromCalendar: {
        url: API_URL + '/calendar/:id',
        method: 'DELETE'
      },
      favorite: {
        url: API_URL + '/spots/:id/favorite'
      },
      unfavorite: {
        url: API_URL + '/spots/:id/unfavorite'
      }
    });
  }

})();
