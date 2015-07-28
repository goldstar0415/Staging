(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Friends', Friends);

  /** @ngInject */
  function Friends($resource, API_URL) {
    return $resource(API_URL + '/friends', {id: '@id'}, {
      update: {
        url: API_URL + '/friends/:id',
        method: 'PUT'
      },
      getFriend: {
        url: API_URL + '/friends/:id',
        method: "GET"
      },
      updateFriend: {
        url: API_URL + '/friends/:id',
        method: "PUT"
      },
      deleteFriend: {
        url: API_URL + '/friends/:id',
        method: "DELETE"
      }
    });
  }

})();
