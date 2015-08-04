(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('StaticPage', StaticPage);

  /** @ngInject */
  function StaticPage($resource, API_URL) {
    return $resource(API_URL + '/static-page/:id', {id: '@id'}, {
      contactUs: {
        url: API_URL + '/contact-us',
        method: 'POST'
      },
      update: {
        method: 'PUT'
      }
    });
  }

})();
