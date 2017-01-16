(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .run(runBlock);

  /** @ngInject */
  function runBlock($log, $http, MapService, UserService, $rootScope, snapRemote, $state, toastr, DEBUG, API_URL, SpotService, SignInService, PermissionService, $modalStack, USER_ONLINE_MINUTE) {
    $rootScope.$state = $state;
    $rootScope.checkPermission = PermissionService.checkPermission;
    $rootScope.isMobile = angular.element(window).width() <= 992;
    L.Icon.Default.imagePath = '/assets/libs/Leaflet/images';
    $rootScope.plannerIcon = '/assets/img/icons/planner_icon.png';
    $rootScope.isSidebarOpened = false;
    $rootScope.toggleSidebar = toggleSidebar;
    $rootScope.openedSpot = null;
    $rootScope.setOpenedSpot = setOpenedSpot;
    $rootScope.showMarkers = showMarkers;
    $rootScope.isFullScreen = false;
    $rootScope.isFilterOpened = false;
    $rootScope.visibleSpotsIds = [];
    $rootScope.spotsCarousel = {};
    $rootScope.spotsCarousel.index = 0;
    $rootScope.highlightedSpotId = null;
    $rootScope.sidebarMessage = "Loading..."
    $rootScope.isMapState = isMapState;
    $rootScope.searchLimit = 12;
    $rootScope.filterOptions = {
        name: '',
        location: '',
        isFavorited: false,
        minRating: "0",
        category: '',
        tags: [],
        dateFrom: '',
        dateTo: ''
    };
    $rootScope.isRadarShown = false;
    $rootScope.weatherUnits = 'us';
    $rootScope.weatherLocation = {
        lat: null,
        lng: null
    };

    $rootScope.categoryData = [];
    $rootScope.getCategoryData = function() {
        $rootScope.categoryData = [];
        if ($rootScope.mapSortSpots && $rootScope.mapSortSpots.data) {
            $rootScope.mapSortSpots.data.forEach(function(element){
                if ($rootScope.categoryData.indexOf(element.category.name) === -1) {
                    $rootScope.categoryData.push({id: element.category.name, label: element.category.name});
                }
            })
        }
    }

    $rootScope.getCategoryData();

    // $rootScope.categoryData = function() {
    //     var spots = $rootScope.spotCategories;
    //     debugger;
    //     var names = [];
    //     spots.forEach(function(element){
    //         if (names.indexOf(element.name) === -1) {
    //             names.push({id: element.name, label: element.name});
    //         }
    //     })
    //     return names;
    // };
    $rootScope.example1model = [];

    MapService.Init('map');

    $rootScope.$on('$stateChangeSuccess', onStateChangeSuccess);
    $rootScope.$on("$stateChangeError", onStateChangeError);

    $rootScope.$watch('$root.spotsCarousel.index', function() {
        MapService.highlightSpot();
    }, true);

    function isMapState() {
        return $rootScope.$state.current.name === 'index' || $rootScope.$state.current.name === 'areas.preview';
    }

    document.addEventListener("fullscreenchange", detectFullScreen);
    document.addEventListener("webkitfullscreenchange", detectFullScreen);
    document.addEventListener("mozfullscreenchange", detectFullScreen);
    document.addEventListener("MSFullscreenChange", detectFullScreen);

    function detectFullScreen() {
        if (
        	document.fullscreenElement ||
        	document.webkitFullscreenElement ||
        	document.mozFullScreenElement ||
        	document.msFullscreenElement
        ) {
            $rootScope.isFullScreen = true;
        } else {
            $rootScope.isFullScreen = false;
        }
    }

    detectFullScreen();

    function setOpenedSpot(item) {
        $rootScope.openedSpot = item;
    }

    function toggleSidebar(isOpened) {
        $rootScope.isSidebarOpened = isOpened;
        if (isOpened) {
            angular.element('.map-tools-top').removeClass('hidden');
            angular.element('.map-tools').addClass('hidden');
            if ($rootScope.sortLayer === 'weather' || $rootScope.$state.current.name === 'areas.preview') {
                angular.element('.save-selection').parent().addClass('hidden');
                angular.element('.filter-selection').parent().addClass('hidden');
                $rootScope.mapState = "small-size";
            } else {
                angular.element('.save-selection').parent().removeClass('hidden');
                angular.element('.filter-selection').parent().removeClass('hidden');
                // $rootScope.mapState = "full-size";
            }
        } else {
            // MapService.removeHighlighting();
            angular.element('.map-tools-top').addClass('hidden');
            angular.element('.map-tools').removeClass('hidden');
            $rootScope.mapState = "full-size";
        }
    }

    function onStateChangeSuccess(event, current, toParams, fromState, fromParams) {
      snapRemote.getSnapper().then(function (snapper) {
        snapper.close();
      });

      $rootScope.previous = {
        state: fromState,
        params: fromParams
      };

      $rootScope.mapSortSpots = {};
      $rootScope.mapSortFilters = {};
      $rootScope.currentParams = toParams;

      if (current.require_auth && !$rootScope.currentUser) {
        toastr.error('Unauthorized!');
        $state.go('index');
      }
      if ($rootScope.currentUser && current.parent != 'profile') {
        UserService.setProfileUser($rootScope.currentUser);
      }

      MapService.clearLayers();
      angular.element('.map-tools').hide();
      $rootScope.changeMapState(current.mapState, current, true);


      if ($state.params.spotSearch) {
        initIntroPage();
      }

      //scroll top
      window.scrollTo(0, 0);

      //close all modals
      $modalStack.dismissAll();

      //close editor
      if (angular.isDefined(window.ContentTools)) {
        ContentTools.EditorApp.get().destroy();
      }

      $rootScope.pageLoaded = true;
    }

    function onStateChangeError(event, toState, toParams) {
      console.warn('$stateChangeError', event);
      if (toState.require_auth && !$rootScope.currentUser) {
        toastr.error('Unauthorized!');
        $state.go('index');
      } else {
        toastr.error('Not found');
      }

      $rootScope.pageLoaded = true;
    }

    $rootScope.options = {
      snap: snapRemote.globalOptions
    };

    //make menu on left sid when small screen
    $rootScope.windowWidth = $(window).width();
    $rootScope.options.snap.disable = "left";
    $(window).resize(_.throttle(onWindowResize, 100));
    function onWindowResize() {
      $rootScope.windowWidth = $(window).width();
      MapService.InvalidateMapSize();
	  if(!$rootScope.$$phase) {
		$rootScope.$apply();
	  }
    }


    $rootScope.goBack = function () {
      if ($rootScope.previous && $rootScope.previous.state && $rootScope.previous.state.name) {
        $state.go($rootScope.previous.state.name, $rootScope.previous.params);
      }
    };

    ////// COMMON FUNCTIONS

    //intro page params
    function initIntroPage() {
      if ($state.params.spotSearch.radiusSelection) {
        $rootScope.RadiusSelectionTool();
      }
      if ($state.params.spotSearch.pathSelection) {
        $rootScope.PathSelectionTool();
      }
      if ($state.params.spotSearch.activeSpotType) {

      }
      if ($state.params.spotSearch.openSignIn) {
        SignInService.openModal();
      }
      if ($state.params.spotSearch.roadSelection) {
        MapService.LoadSelections($state.params.spotSearch.roadSelection);
      }
      if ($state.params.spotSearch.spots) {
        showMarkers($state.params.spotSearch.spots);
      }
    }

    function showMarkers(spots) {
      if (spots.length > 0) {
        $rootScope.$emit('update-map-data', spots, $state.params.spotSearch.activeSpotType, false);
        MapService.FitBoundsByLayer($state.params.spotSearch.activeSpotType);
      } else {
        toastr.error('Spots not found');
      }
    }

    //show/hide map
    $rootScope.changeMapState = function (mapState, urlState, isClearLayers) {
      MapService.ChangeState(mapState, isClearLayers);

      if (urlState) {
          if (urlState.name == 'index' && mapState == 'big') {
            angular.element('.map-tools').show();

            if (!$state.params.spotSearch && !$state.params.spotLocation) {
              MapService.FocusMapToCurrentLocation(12);
            }
          } else if (!$state.params.spotSearch) {
            $rootScope.showHintPopup = false;
          }
      }
    };

    $rootScope.toggleMapState = function () {
      var mapState = $rootScope.mapState == 'full-size' ? 'small' : 'big';
      $rootScope.changeMapState(mapState, $state.current, false);

      //load all user spots when open map on profile
      console.log('STATE:', mapState, $state.current.name);
      if (mapState == 'big' && ($state.current.name == 'profile.main' || $state.current.name == 'spots' || $state.current.name == 'favorites')) {
        $rootScope.$emit('change-map-state', mapState);
      }
    };

    //isEmpty
    $rootScope.isEmpty = function (obj) {
      // Speed up calls to hasOwnProperty
      var hasOwnProperty = Object.prototype.hasOwnProperty;

      if (obj == null) return true;
      if (obj.length > 0)    return false;
      if (obj.length === 0)  return true;

      for (var key in obj) {
        if (hasOwnProperty.call(obj, key)) return false;
      }

      return true;
    };

    //check user online
    $rootScope.isOnline = function (user) {
      var online = false;
      if (user.last_action_at) {
        var lastAction = moment(user.last_action_at);
        online = (lastAction.diff(moment(), 'minutes') + moment().utcOffset()) >= USER_ONLINE_MINUTE;
      }

      return {online: online, offline: !online};
    };

    //check user role
    $rootScope.isRole = function (user, name) {
      if (user) {
        var roles = _.pluck(user.roles, 'name');
        return roles.length > 0 && roles.indexOf(name) >= 0;
      }
    };

    $rootScope.isActiveState = function (state) {
      return {
        //active: $state.includes()
        active: state == $state.current.name || $state.current.name.indexOf(state + '.') >= 0
      };
    };

    $rootScope.$apply();
  }

})();
