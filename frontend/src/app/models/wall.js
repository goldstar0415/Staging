(function () {
  'use strict';

  /*
   * Wall model
   */
  angular
    .module('zoomtivity')
    .factory('Wall', Wall);

  /** @ngInject */
  function Wall($resource, API_URL) {
    return $resource(API_URL + '/wall/:id', {id: '@id'}, {
      query: {
        isArray: false,
        ignoreLoadingBar: true,
        params: {
          page: 1,
          limit: 10
        }
      },
      like: {
        method: 'POST',
        url: API_URL + '/wall/:id/like',
        ignoreLoadingBar: true
      },
      dislike: {
        method: 'POST',
        url: API_URL + '/wall/:id/dislike',
        ignoreLoadingBar: true
      }
    });
  }

})();
