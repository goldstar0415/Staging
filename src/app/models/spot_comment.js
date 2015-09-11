(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SpotComment', SpotComment);

  /** @ngInject */
  function SpotComment($resource, API_URL) {
    return $resource(API_URL + '/spots/:spot_id/comments/:id', {id: '@id', spot_id: '@spot_id'}, {
      query: {
        isArray: false,
        params: {
          page: 1,
          limit: 10
        }
      }
    });
  }

})();
