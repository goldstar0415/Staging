(function () {
  'use strict';

  /*
   * Area model
   */
  angular
    .module('zoomtivity')
    .factory('Area', Area);

  /** @ngInject */
  function Area($resource, API_URL) {
    return $resource(API_URL + '/areas/:area_id', {area_id: '@area_id'}, {
      update: {
        method: 'PUT'
      }
    });
  }

})();
