(function () {
  'use strict';

  /*
   * Hotel model
   */
  angular
    .module('zoomtivity')
    .factory('Hotel', Hotel);

  /** @ngInject */
  function Hotel($resource, API_URL) {
    return $resource(API_URL + '/hotels/:id', {id: '@id'}, {
      query: {
        isArray: false
      },
      paginate: {
        isArray: false
      }
    });
  }

})();