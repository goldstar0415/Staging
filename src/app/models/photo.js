(function () {
  'use strict';

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
      },
      getComments: {
        url: API_URL + '/photos/:id/comments',
        method: 'GET',
        isArray: true
      },
      deleteComment: {
        url: API_URL + '/photos/:id/comments/:comment_id',
        method: 'DELETE'
      },
      postComment: {
        url: API_URL + '/photos/:id/comments',
        method: 'POST'
      }
    });
  }
})();
