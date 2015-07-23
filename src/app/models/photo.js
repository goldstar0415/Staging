(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Photo', Photo);

  /** @ngInject */
  function Photo($resource, API_URL) {
    return $resource(API_URL + '/photos/:id', {id: '@id'}, {
      setAsAvatar: {
        url: API_URL + '/photos/:id/avatar',
        method: 'GET'
      },
      update: {
        method: 'PUT'
      },
      getComments: {
        url: API_URL + '/photos/:id/comments',
        method: 'GET'
      },
      deleteComment: {
        url: API_URL + '/comments/:id',
        method: 'DELETE'
      },
      postComment: {
        url: API_URL + '/photos/:id/comments',
        method: 'POST'
      }
    });
  }
})();
