(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Spot', Spot);

  /** @ngInject */
  function Spot($resource, API_URL) {
    return $resource(API_URL + '/spots/:id', {id: '@id'}, {

    });
  }

})();
