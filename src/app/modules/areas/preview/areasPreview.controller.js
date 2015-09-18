(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('AreasPreviewController', AreasPreviewController);

  /** @ngInject */
  function AreasPreviewController(selection, $rootScope, MapService) {
    var vm = this;

    $rootScope.hideHints = true;
    MapService.LoadSelections(selection);
    //MapService.FitBoundsOfDrawLayer();
  }
})();
