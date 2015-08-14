(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Plan', Plan);

  /** @ngInject */
  function Plan($resource, API_URL) {
    return $resource(API_URL + '/plans/:id', {id: '@id'}, {
      events: {
        url: API_URL + '/calendar/plans',
        isArray: false
      }
    });
  }

})();
