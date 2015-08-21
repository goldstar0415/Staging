(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Feed', Feed);

  /** @ngInject */
  function Feed($resource, API_URL) {
    return $resource(API_URL + '/feeds', {}, {
      query: {
        url: API_URL + '/feeds',
        isArray: false,
        params: {
          page: 1,
          limit: 10
        }
      },
      reviews: {
        url: API_URL + '/spots/reviews',
        method: 'GET',
        isArray: true,
        params: {
          page: 1,
          limit: 20
        }
      }
    });
  }

})();
