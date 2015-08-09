(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Message', Message);

  /** @ngInject */
  function Message($resource, API_URL) {
    return $resource(API_URL + '/message/:id', {id: '@id'}, {
      dialogs: {
        url: API_URL + '/message/dialogs',
        isArray: true
      },
      query: {
        url: API_URL + '/message/list'
      },
      save: {
        method: 'POST',
        ignoreLoadingBar: true
      },
      markAsRead: {
        url: API_URL + '/message/:user_id/read',
        ignoreLoadingBar: true
      }
    });
  }

})();
