(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Wall', Wall);

  /** @ngInject */
  function Wall($resource, API_URL) {
    return $resource(API_URL + '/wall/:id', {id: '@id'}, {
      query: {
        isArray:false,
        params: {
          page: 1,
          limit: 10
        }
      },
      like: {
        url: API_URL + '/wall/:id/like',
        ignoreLoadingBar: true
      },
      dislike: {
        url: API_URL + '/wall/:id/dislike',
        ignoreLoadingBar: true
      }
    });
  }

})();
