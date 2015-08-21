(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Plan', Plan);

  /** @ngInject */
  function Plan($resource, API_URL) {
    return $resource(API_URL + '/plans/:id', {id: '@id'}, {
      query: {
        params: {
          page: 1,
          limit: 10
        }
      },
      events: {
        url: API_URL + '/calendar/plans',
        isArray: false
      },
      activityCategories: {
        url: API_URL + '/activity-categories',
        isArray: true
      },
      update: {
        method: 'PUT'
      }
    });
  }

})();
