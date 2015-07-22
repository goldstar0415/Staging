(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Album', Album);

  /** @ngInject */
  function Album($resource, API_URL) {
    return $resource(API_URL + '/albums/:id', {id: '@id'});
  }

})();
