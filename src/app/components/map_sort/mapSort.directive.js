(function () {
  'use strict';

  /*
   * Directive for spot control panel
   */
  angular.module('zoomtivity')
    .directive('mapSort', function () {
      return {
        restrict: 'E',
        templateUrl: '/app/components/map_sort/map_sort.html',
        controller: mapSort,
        controllerAs: 'MapSort',
        bindToController: true,
        scope: {}
      }
    });

  function mapSort($rootScope, $q, MapService, $http, $timeout, Spot, SpotService, API_URL, DATE_FORMAT, $stateParams) {

    console.log('MapSort params', $stateParams);
    console.log('Map Service', MapService);

    var vm = this;
    var SEARCH_URL = API_URL + '/map/spots';
    var SPOT_LIST_URL = API_URL + '/map/spots/list';
    var SPOTS_PER_PAGE = 10;
    var restrictions = {
      tags: 7,
      locations: 20
    };
    var isSelectedAll = false;

    vm.vertical = true;
    vm.weatherForecast = [];
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.search = search;
    vm.selectAllCategories = selectAllCategories;
    vm.invalidTag = invalidTag;
    vm.onTagsAdd = onTagsAdd;
    vm.addLocation = addLocation;
    vm.removeLocation = removeLocation;
    vm.removeFilter = removeFilter;
    vm.removeFilterCategory = removeFilterCategory;
    vm.clearFilters = clearFilters;
    vm.isEmptyFilters = isEmptyFilters;
    vm.loadNextSpots = loadNextSpots;

    vm.searchParams = {
      locations: [],
      tags: []
    };

    $rootScope.doSearchMap = search;
    $rootScope.sortLayer = $rootScope.sortLayer || 'event';
    $rootScope.isDrawArea = false;
    $rootScope.mapSortFilters = $rootScope.mapSortFilters || {};
    $rootScope.toggleLayer = toggleLayer;

    $rootScope.$on('update-map-data', onUpdateMapData);

    run();


    function run() {
      loadCategories();
      vm.searchParams.search_text = ($stateParams.searchText || '');
      if (vm.searchParams.search_text.length > 0) {
        vm.vertical = false;
        MapService.FocusMapToCurrentLocation();
        toggleLayer();
        search();
      }
      /**
      if (_.isObject($stateParams.spotLocation)) {
        MapService.FocusMapToGivenLocation($stateParams.spotLocation);
        MapService.GetBoundsByCircle($stateParams.spotLocation, getCircleBounds);
      } else {
        MapService.FocusMapToCurrentLocation();
      }
      **/
    }

    function getCircleBounds(bounds) {
      console.log('Circle Bounds', bounds);
      search();
    }

    function onUpdateMapData(event, mapSpots, layer, isDrawArea) {
      console.log('update map');
      layer = layer || $rootScope.sortLayer;
      $rootScope.sortLayer = layer;
      if (angular.isDefined(isDrawArea)) {
        $rootScope.isDrawArea = isDrawArea;
      }

      $rootScope.mapSortSpots = {
        markers: [],
        data: [],
        page: 0,
        cancellerHttp: $rootScope.mapSortSpots.cancellerHttp
      };
      if ($rootScope.isDrawArea) {
        _.each(mapSpots, function (item) {
          if (MapService.PointInPolygon(item.location)) {
            $rootScope.mapSortSpots.markers.push(item);
          }
        });
      } else {
        $rootScope.mapSortSpots.markers = mapSpots;
      }

      $timeout(function () {
        if ($rootScope.mapSortSpots.markers.length > 0) {
          MapService.drawSearchSpotMarkers($rootScope.mapSortSpots.markers, layer, true);
          if (!$rootScope.isDrawArea) {
            MapService.FitBoundsByLayer($rootScope.sortLayer);
          }
        } else {
          toastr.info('0 spots found');
          MapService.clearLayers();
        }
      });

      $rootScope.mapSortSpots.sourceSpots = _filterUniqueSpots($rootScope.mapSortSpots.markers);
      loadNextSpots();
    }

    function _filterUniqueSpots(array) {
      return _.uniq(array, function (item) {
        return item.spot_id
      });
    }

    function loadNextSpots() {
      console.log('loadNextSpots');
      if ($rootScope.mapSortSpots.sourceSpots && $rootScope.mapSortSpots.sourceSpots.length > 0) {
        var startIdx = $rootScope.mapSortSpots.page * SPOTS_PER_PAGE,
        endIdx = startIdx + SPOTS_PER_PAGE,
        spots = $rootScope.mapSortSpots.sourceSpots.slice(startIdx, endIdx),
        ids = _.pluck(spots, 'spot_id');

        if (ids.length > 0) {
          $rootScope.mapSortSpots.isLoading = true;
          $http.get(SPOT_LIST_URL + '?' + jQuery.param({ids: ids}))
            .success(function success(data) {
              if ($rootScope.sortLayer == 'event') {
                data = SpotService.formatSpot(data);
              }

              $rootScope.mapSortSpots.data = _.union($rootScope.mapSortSpots.data, data);
              $rootScope.mapSortSpots.isLoading = false;
            })
            .catch(function (resp) {
              $rootScope.mapSortSpots.isLoading = false;
            });

          $rootScope.mapSortSpots.page++;
        }
      }
    }

    function toggleLayer(layer) {
      console.log('toggle layer');
      $rootScope.sortLayer = layer;

      if (layer == 'weather') {
        MapService.showOtherLayers();
        MapService.WeatherSelection(weather);

        if (!vm.currentWeather) {
          toastr.info('Click on map to check weather in this area');
        }
      } else {
        if (layer != 'event') {
          $rootScope.mapSortFilters.filter = $rootScope.mapSortFilters.filter || {};
          $rootScope.mapSortFilters.filter.start_date = $rootScope.mapSortFilters.filter.end_date = '';
          vm.searchParams.start_date = vm.searchParams.end_date = '';
        }

        search();
        MapService.showLayer(layer);

        var wp = MapService.GetPathWaypoints();
        var geoJson = MapService.GetGeoJSON();

        if ($rootScope.isDrawArea && wp.length < 1 && geoJson && geoJson.features.length < 1) {
          toastr.info('Draw the search area');
        }
      }
    }

    function onTagsAdd(q, w, e) {
      console.log('add tags');
      if (vm.searchParams.tags.length < restrictions.tags) {
        return true;
      } else {
        toastr.error('You can\'t add more than ' + restrictions.tags + ' tags');
        return false;
      }
    }

    function invalidTag(tag) {
      if (tag.text.length > 64) {
        toastr.error('Your tag is too long. Max 64 symbols.');
      } else {
        toastr.error('Invalid input.');
      }
    }

    function loadCategories() {
      console.log('Load Categories');
      if (!$rootScope.spotCategories) {
        $http.get(API_URL + '/spots/categories')
          .success(function (data) {
            $rootScope.spotCategories = data;
            console.log('Categories', data);
            _loadCategories(data)
          });
      } else {
        _loadCategories($rootScope.spotCategories);
      }
    }

    function _loadCategories(data) {
      vm.spotCategories = {};
      _.each(data, function (item) {
        vm.spotCategories[item.name] = item.categories;
      });
    }


    function search() {
      console.log('searching');
      if (vm.searchParams.location || vm.searchParams.locations.length > 0) {
        drawPathSelection(doSearch);
      } else {
        doSearch();
      }
    }

    function doSearch() {
      console.log('do search');
      var data = {
        search_text: vm.searchParams.search_text,
        filter: {}
      };

      if (vm.searchParams.rating) {
        data.filter.rating = vm.searchParams.rating;
      }

      if (vm.searchParams.tags) {
        data.filter.tags = _.pluck(vm.searchParams.tags, 'text');
      }

      if (vm.searchParams.start_date) {
        data.filter.start_date = moment(vm.searchParams.start_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
      }
      if (vm.searchParams.end_date) {
        data.filter.end_date = moment(vm.searchParams.end_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
      }

      var categories = _.where(vm.spotCategories[$rootScope.sortLayer], {selected: true});
      if (categories.length > 0) {
        data.filter.category_ids = _.pluck(categories, 'id');
      }

      $rootScope.mapSortFilters = angular.copy(data);

      var bbox_array = MapService.GetBBoxes();
      if (bbox_array.length > 0) {
        bbox_array = MapService.BBoxToParams(bbox_array);
        data.filter.b_boxes = bbox_array;
        data.search_text = '';
      }

      if (bbox_array.length == 0 && !vm.searchParams.search_text) {
        toastr.error('Enter location or draw the area');
        $rootScope.mapSortFilters = {};
        return;
      }
      data.type = $rootScope.sortLayer;

      MapService.cancelHttpRequest();
      $rootScope.mapSortSpots.cancellerHttp = $q.defer();

      $http.get(SEARCH_URL + '?' + jQuery.param(data), {timeout: $rootScope.mapSortSpots.cancellerHttp.promise})
        .success(function (spots) {
          if (spots.length > 0) {
            onUpdateMapData(null, spots, $rootScope.sortLayer, bbox_array.length > 0);
          } else {
            onUpdateMapData(null, [], null, bbox_array.length > 0);
          }
          vm.categoryToggle = false;
          vm.isShowFilter = false;
        }).catch(function (resp) {
          if (resp.status > 0) {
            toastr.error(resp.data ? resp.data.message : 'Something went wrong')
          }
        });
    }

    function drawPathSelection(callback) {
      console.log('Draw Path');
      var points = [];
      if (vm.searchParams.location && vm.searchParams.location) {
        points.push(vm.searchParams.location);
      }
      if (vm.searchParams.locations.length > 0) {
        points = _.union(points, _.pluck(vm.searchParams.locations, 'location'));
      }

      MapService.clearLayers();
      MapService.PathSelection(points, callback);
    }

    function addLocation() {
      console.log('adding location');
      if (vm.searchParams.address && vm.searchParams.location) {
        vm.searchParams.locations.unshift({
          address: vm.searchParams.address,
          location: vm.searchParams.location
        });
        vm.searchParams.address = '';
        vm.searchParams.location = {};

        angular.element('#new_location input').attr('placeholder', 'Add next destination'); //fix location placeholder
      } else {
        toastr.error('Wrong location');
        vm.searchParams.address = '';
        vm.searchParams.location = {};
      }
    }

    function removeLocation(idx) {
      vm.searchParams.locations.splice(idx, 1);
      if (vm.searchParams.locations.length == 0) {
        angular.element('#new_location input').attr('placeholder', 'Add first destination');  //fix location placeholder
      }
    }

    function clearFilters() {
      $rootScope.mapSortFilters = {};
      vm.searchParams = {
        locations: [],
        tags: []
      };

      //clear categories
      isSelectedAll = true;
      selectAllCategories();

      if ($rootScope.isDrawArea) {
        search();
      } else {
        MapService.clearLayers();
        MapService.cancelHttpRequest();
      }
    }

    function removeFilter(type) {
      switch (type) {
        case 'date':
          if ($rootScope.mapSortFilters.filter) {
            $rootScope.mapSortFilters.filter.start_date = '';
            $rootScope.mapSortFilters.filter.end_date = '';
            vm.searchParams.start_date = '';
            vm.searchParams.end_date = '';
          }
          break;
        case 'tags':
          $rootScope.mapSortFilters.filter.tags = [];
          vm.searchParams.tags = [];
          break;
        case 'rating':
          $rootScope.mapSortFilters.filter.rating = null;
          vm.searchParams.rating = null;
          break;
      }

      search();
    }

    function removeFilterCategory(item) {
      item.selected = false;
      if ($rootScope.mapSortFilters && $rootScope.mapSortFilters.filter && $rootScope.mapSortFilters.filter.category_ids) {
        $rootScope.mapSortFilters.filter.category_ids = _.without($rootScope.mapSortFilters.filter.category_ids, item.id);
      }

      search();
    }

    function selectAllCategories() {
      isSelectedAll = !isSelectedAll;
      _.each(vm.spotCategories[$rootScope.sortLayer], function (item) {
        item.selected = isSelectedAll;
      });
    }

    function isEmptyFilters() {
      var isEmpty = true;

      $rootScope.mapSortFilters.filter = $rootScope.mapSortFilters.filter || {};

      if ($rootScope.mapSortFilters.search_text ||
        $rootScope.mapSortFilters.filter.start_date || $rootScope.mapSortFilters.filter.end_date ||
        $rootScope.mapSortFilters.filter.rating ||
        ($rootScope.mapSortFilters.filter.tags && $rootScope.mapSortFilters.filter.tags.length > 0) ||
        ($rootScope.mapSortFilters.filter.category_ids && $rootScope.mapSortFilters.filter.category_ids.length > 0)) {
        isEmpty = false;
      }
      return isEmpty;
    }


    //============================ weather section =========================
    function weather(resp) {
      vm.vertical = false;
      vm.weatherForecast = [];
      var daily = resp.daily.data;

      for (var k in daily) {
        daily[k].formattedDate = moment(daily[k].time * 1000).format('ddd');
        if (k != 0) {
          vm.weatherForecast.push(daily[k]);
        }
      }
      vm.currentWeather = daily[0];
      vm.currentWeather.sunrise = moment(daily[0].sunriseTime * 1000).format(DATE_FORMAT.time);
      vm.currentWeather.sunset = moment(daily[0].sunsetTime * 1000).format(DATE_FORMAT.time);
      vm.currentWeather.temperature = Math.round((daily[0].temperatureMax + daily[0].temperatureMin) / 2);
    }
  }
})();
