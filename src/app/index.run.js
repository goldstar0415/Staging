(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .run(runBlock);

  /** @ngInject */
  function runBlock($log, User, MapService, $rootScope, snapRemote) {

    MapService.Init('map');

    $rootScope.$on('$stateChangeSuccess', onStateChangeSuccess);
    function onStateChangeSuccess(event, current, previous) {
      MapService.ChangeState(current.mapState);
      window.scrollTo(0, 0);
    }

    $rootScope.options = {
      snap: snapRemote.globalOptions
    };

    //make menu on left sid when small screen
    $(window).resize(_.throttle(onWindowResize, 100));
    function onWindowResize() {
      MapService.InvalidateMapSize();
      if ($(window).width() < 768) {
        $rootScope.options.snap.disable = "right";
      } else {
        $rootScope.options.snap.disable = "left";
      }
      $rootScope.$apply();
    }

    //get current user
    User.currentUser().$promise.then(function (user) {
      console.log(user);
      $rootScope.currentUser = user;
    });

    $rootScope.$apply();
  }

})();
