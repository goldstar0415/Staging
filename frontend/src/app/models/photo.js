(function () {
  'use strict';

  /*
   * Photo model
   */
  angular
    .module('zoomtivity')
    .factory('Photo', Photo);

  /** @ngInject */
  function Photo($resource, API_URL) {
    return $resource(API_URL + '/photos/:id', {id: '@id', comment_id: '@comment_id'}, {
      setAsAvatar: {
        url: API_URL + '/photos/:id/avatar',
        method: 'GET'
      },
      update: {
        method: 'PUT'
      }
    });
  }
})();
