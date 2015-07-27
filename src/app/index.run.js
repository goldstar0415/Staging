(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .run(runBlock);

  /** @ngInject */
  function runBlock($log, User, MapService, $rootScope, snapRemote, $state, toastr) {

    MapService.Init('map');
    $rootScope.timezonesList = moment.tz.names();

    $rootScope.$on('$stateChangeSuccess', onStateChangeSuccess);
    function onStateChangeSuccess(event, current, previous) {
      if(current.require_auth) {
        if(!$rootScope.currentUser) {
          toastr.error('Unauthorized!');
          $state.go('index');
        }
      }

      MapService.ChangeState(current.mapState);
      window.scrollTo(0, 0);
      if(current.mapState == 'big'){
        $('.map-tools').show();
      } else {
        $('.map-tools').hide();
      }

      switch (current.locate) {
        case 'fit':
              MapService.FitBoundsOfCurrentLayer();
              break;
        default:
              MapService.FocusMapToCurrentLocation();
              break;
      };

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
      $rootScope.currentUser = user;
      if($rootScope.currentUser.location && $rootScope.currentUser.location.coordinates){
        $rootScope.currentUser.location = {
          lat: $rootScope.currentUser.location.coordinates[0],
          lng: $rootScope.currentUser.location.coordinates[1]
        }

      }
    });

    $rootScope.$apply();
  }

})();
