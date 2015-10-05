(function () {
  'use strict';

  /*
   * Controller for preview saved searches
   */
  angular
    .module('zoomtivity')
    .controller('AreasPreviewController', AreasPreviewController);

  /** @ngInject */
  function AreasPreviewController(selection, $rootScope, MapService, $timeout) {
    $rootScope.hideHints = true;
    MapService.LoadSelections(selection);
  }
})();
