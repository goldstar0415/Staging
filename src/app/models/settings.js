(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Settings', Settings);

  /** @ngInject */
  function Settings($resource, API_URL) {
    return $resource(API_URL + '/settings', {
      save: {
        url: API_URL + '/settings',
        method: 'PUT',
        params: {type: ""}
      }
    })
  }

})();
