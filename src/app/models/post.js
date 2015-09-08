(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Post', Post);

  /** @ngInject */
  function Post($resource, API_URL) {
    return $resource(API_URL + '/posts/:id', {id: '@id'}, {
      categories: {
        url: API_URL + '/posts/categories',
        isArray: true
      },
      popular: {
        url: API_URL + '/posts/popular',
        isArray: true
      }
    });
  }

})();
