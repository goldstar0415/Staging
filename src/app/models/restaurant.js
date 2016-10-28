(function () {
  'use strict';

  /*
   * Restaurant model
   */
  angular
    .module('zoomtivity')
    .factory('Restaurant', Restaurant);

  /** @ngInject */
  function Restaurant($resource, API_URL) {
    return $resource(API_URL + '/restaurants/:id', {id: '@id'}, {
      query: {
        isArray: false
      },
      paginate: {
        isArray: false
      }
    });
  }

})();