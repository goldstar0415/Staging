(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .run(runBlock);

  /** @ngInject */
  function runBlock($log, MapService, UserService, $rootScope, snapRemote, $state, toastr, DEBUG, UploaderService, $modalStack) {

    $rootScope.isMobile = L.Browser.touch;
    L.Icon.Default.imagePath = '/assets/libs/Leaflet/images';
    $rootScope.plannerIcon = '/assets/img/icons/planner_icon.png';

    MapService.Init('map');
    //$rootScope.timezonesList = moment.tz.names();

    $rootScope.$on('$stateChangeSuccess', onStateChangeSuccess);
    $rootScope.$on("$stateChangeError", onStateChangeError);

    function onStateChangeSuccess(event, current, toParams, fromState, fromParams) {
      snapRemote.getSnapper().then(function (snapper) {
        snapper.close();
      });
      UploaderService.images.files = [];

      $rootScope.previous = {
        state: fromState,
        params: fromParams
      };

      $rootScope.currentParams = toParams;

      if (current.require_auth && !$rootScope.currentUser) {
        console.log($rootScope.currentUser, arguments);
        toastr.error('Unauthorized!');
        $state.go('index');
      }

      if ($rootScope.currentUser && current.parent != 'profile') {
        UserService.setProfileUser($rootScope.currentUser);
      }

      MapService.clearLayers();
      MapService.ChangeState(current.mapState);

      if (current.mapState == 'big') {
        angular.element('.map-tools').show();
        MapService.FocusMapToCurrentLocation(12);
      } else {
        angular.element('.map-tools').hide();
      }

      switch (current.locate) {
        case 'fit':
          MapService.FitBoundsOfCurrentLayer();
          break;
        case 'none':
          break;
        default:
          //MapService.FocusMapToCurrentLocation(4);
          break;
      }

      //scroll top
      window.scrollTo(0, 0);

      //close all modals
      $modalStack.dismissAll();

      $rootScope.pageLoaded = true;
    }

    function onStateChangeError(event, toState, toParams) {
      console.log('$stateChangeError', event);
      if (toState.require_auth && !$rootScope.currentUser) {
        toastr.error('Unauthorized!');
        $state.go('index');
      }

      $rootScope.pageLoaded = true;
    }

    $rootScope.options = {
      snap: snapRemote.globalOptions
    };

    //make menu on left sid when small screen
    $rootScope.windowWidth = $(window).width();
    $(window).resize(_.throttle(onWindowResize, 100));
    function onWindowResize() {
      $rootScope.windowWidth = $(window).width();
      MapService.InvalidateMapSize();
      if ($rootScope.windowWidth < 992) {
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
