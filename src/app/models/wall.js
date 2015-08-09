(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Wall', Wall);

  /** @ngInject */
  function Wall($resource, API_URL) {
    return $resource(API_URL + '/wall/:id', {id: '@id', user_id: '@user_id'}, {

    });
  }

})();
