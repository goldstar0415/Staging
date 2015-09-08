(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Plan', Plan);

  /** @ngInject */
  function Plan($resource, API_URL) {
    return $resource(API_URL + '/plans/:id', {id: '@id'}, {
      query: {
        isArray: false
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
      },
      inviteFriends: {
        url: API_URL + '/plans/invite',
        method: 'POST'
      }
    });
  }

})();
