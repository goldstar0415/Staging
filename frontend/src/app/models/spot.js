(function () {
  'use strict';

  /*
   * Spot model
   */
  angular
    .module('zoomtivity')
    .factory('Spot', Spot);

  /** @ngInject */
  function Spot($resource, API_URL) {
    return $resource(API_URL + '/spots/:id', {id: '@id'}, {
      paginate: {
        isArray: false
      },
      favorites: {
        url: API_URL + '/spots/favorites'
      },
      members: {
        url: API_URL + '/spots/:id/members',
        isArray: true
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
      },
      reviews: {
        url: API_URL + '/spots/:id/reviews',
        method: 'POST',
        ignoreLoadingBar: true
      },
      report: {
        url: API_URL + '/spots/:id/report',
        method: 'POST'
      },
      claim: {
        url: API_URL + '/spots/:id/owner',
        method: 'POST'
      }
    });
  }

})();
