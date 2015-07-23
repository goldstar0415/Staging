(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('PhotoComments', PhotoComments);

  /** @ngInject */
  function PhotoComments($resource, API_URL) {
    return $resource(API_URL + '/comments/:id', {id: '@id', photo_id: '@photo_id'}, {
      getComments: {
        url: API_URL + '/photos/:photo_id/comments',
        method: 'GET'
      }
      //deleteComment: {
      //  url: API_URL + '/photos/:id/comments/:comment_id',
      //  method: 'DELETE'
      //},
      //postComment: {
      //  url: API_URL + '/photos/:id/comments',
      //  method: 'POST'
      //}
    });
  }
})();
