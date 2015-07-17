(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .run(runBlock);

  /** @ngInject */
  function runBlock($log, MapService) {

    $log.debug('runBlock end');

    MapService.Init('map');
  }

})();
