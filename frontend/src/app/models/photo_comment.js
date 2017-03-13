(function () {
  'use strict';

  /*
   * PhotoComment model
   */
  angular
    .module('zoomtivity')
    .factory('PhotoComment', PhotoComment);

  /** @ngInject */
  function PhotoComment($resource, API_URL) {
    return $resource(API_URL + '/photos/:photo_id/comments/:id', {id: '@id', photo_id: '@photo_id'}, {});
  }
})();
