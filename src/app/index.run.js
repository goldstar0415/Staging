(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .run(runBlock);

  /** @ngInject */
  function runBlock($log, User, MapService, $rootScope, snapRemote, $state, toastr, DEBUG) {

    MapService.Init('map');
    $rootScope.timezonesList = moment.tz.names();

    $rootScope.$on('$stateChangeSuccess', onStateChangeSuccess);
    function saveCurrentUser(user) {
      $rootScope.currentUser = user;
      if (!$rootScope.profileUser) {
        $rootScope.profileUser = user;
      }
    }

    function onStateChangeSuccess(event, current, toParams, fromState, fromParams) {
      $rootScope.previous = {
        state: fromState,
        params: fromParams
      };
      if (current.require_auth) {
        if (!$rootScope.currentUser) {
          User.currentUser().$promise
            .then(saveCurrentUser)
            .catch(function () {
              toastr.error('Unauthorized!');
              $state.go('index');
            });
        }
      } else {
        if (!$rootScope.currentUser) {
          User.currentUser().$promise
            .then(saveCurrentUser)
        }
      }

      MapService.ChangeState(current.mapState);

      if (current.mapState == 'big') {
        $('.map-tools').show();
      } else {
        $('.map-tools').hide();
      }

      switch (current.locate) {
        case 'fit':
          MapService.FitBoundsOfCurrentLayer();
          break;
        case 'none':
          break;
        default:
          MapService.FocusMapToCurrentLocation();
          break;
      }
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


    $rootScope.goBack = function () {
      if ($rootScope.previous && $rootScope.previous.state && $rootScope.previous.state.name) {
        $state.go($rootScope.previous.state.name, $rootScope.previous.params);
      }
    };
    $rootScope.$apply();
  }

})();
