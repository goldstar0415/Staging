(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Album', Album);

  /** @ngInject */
  function Album($resource, API_URL) {
    return $resource(API_URL + '/albums/:id', {id: '@id', user_id: '@user_id'}, {
      query: {
        url: API_URL + '/users/:user_id/albums',
        isArray: true
      },
      photos: {
        url: API_URL + '/albums/:album_id/photos',
        isArray: true
      }
    });
  }

})();
