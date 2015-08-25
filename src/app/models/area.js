(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Area', Area);

  /** @ngInject */
  function Area($resource, API_URL) {
    return $resource(API_URL + '/areas/:id', {id: '@id'}, {
      update: {
        method: 'PUT'
      }
    });
  }

})();
