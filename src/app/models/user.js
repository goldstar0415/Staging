(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('UserModel', UserModel);

  /** @ngInject */
  function UserModel($resource, API_URL) {
    return $resource(API_URL + '/users/:id', {id: '@id'}, {
      update: {
        method: 'PUT'
      }
    });
  }

})();
