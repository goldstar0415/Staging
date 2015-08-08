(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Area', Area);

  /** @ngInject */
  function Area($resource, API_URL) {
    return $resource(API_URL + '/selection/:id', {id: '@id'}, {
      update: {
        method: 'PUT'
      }
    });
  }

})();
