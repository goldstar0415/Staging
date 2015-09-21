(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('MapPostController', MapPostController);

  /** @ngInject */
  function MapPostController(post, MapService, $rootScope) {
    $rootScope.hideHints = true;
    var posts = [post];

    MapService.showOtherLayers();
    MapService.drawBlogMarkers(posts, true);
  }
})();
