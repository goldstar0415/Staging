(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Post', Post);

  /** @ngInject */
  function Post($resource, API_URL) {
    return $resource(API_URL + '/posts/:id', {id: '@id'}, {
      paginate: {
        isArray: false
      },
      categories: {
        url: API_URL + '/posts/categories',
        isArray: true
      },
      request: {
        url: API_URL + '/posts/request',
        method: 'POST'
      },
      popular: {
        url: API_URL + '/posts/popular',
        isArray: true
      }
    });
  }

})();
