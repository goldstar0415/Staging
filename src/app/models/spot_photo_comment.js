(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SpotPhotoComments', SpotPhotoComments);

  /** @ngInject */
  function SpotPhotoComments($resource, API_URL) {
    return $resource(API_URL + '/spots/:spot_id/photos/:photo_id/comments/:id', {id: '@id', photo_id: '@photo_id'}, {

    });
  }
})();
