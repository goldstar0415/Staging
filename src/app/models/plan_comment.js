(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('PlanComment', PlanComment);

  /** @ngInject */
  function PlanComment($resource, API_URL) {
    return $resource(API_URL + '/plans/:plan_id/comments/:id', {id: '@id', plan_id: '@plan_id'}, {
      query: {
        isArray:false,
        params: {
          page: 1,
          limit: 10
        }
      }
    });
  }

})();
