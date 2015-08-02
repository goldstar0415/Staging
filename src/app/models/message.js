(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Message', Message);

  /** @ngInject */
  function Message($resource, API_URL) {
    return $resource(API_URL + '/message/:id', {id: '@id'}, {

    });
  }

})();
