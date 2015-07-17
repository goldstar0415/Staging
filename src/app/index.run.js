(function() {
  'use strict';

  angular
    .module('zoomtivity')
    .run(runBlock);

  /** @ngInject */
  function runBlock($log, MapService, $rootScope, snapRemote) {

    $log.debug('runBlock end');

    MapService.Init('map');

    $rootScope.options = snapRemote.globalOptions;
    $rootScope.$apply();

    $(window).resize(_.throttle(onWindowResize, 100));

    function onWindowResize() {
      if($(window).width() < 768) {
        $rootScope.options.disable = "right";
      } else {
        $rootScope.options.disable = "left";
      }
      $rootScope.$apply();
    }
  }

})();
