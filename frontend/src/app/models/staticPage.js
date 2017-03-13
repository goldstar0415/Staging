(function () {
  'use strict';

  /*
   * StaticPage model
   */
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
      terms: {
        url: API_URL + '/terms'
      },
      update: {
        method: 'PUT'
      }
    });
  }

})();
