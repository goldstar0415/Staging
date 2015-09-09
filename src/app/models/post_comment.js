(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('PostComment', PostComment);

  /** @ngInject */
  function PostComment($resource, API_URL) {
    return $resource(API_URL + '/posts/:post_id/comments/:id', {id: '@id', post_id: '@post_id'}, {
      query: {
        isArray:false,
        params: {
          page: 1,
          limit: 10
        }
      }
    });
  }

})();
