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

  function mapSort($rootScope, MapService, $http, SpotService, API_URL, DATE_FORMAT) {
    var vm = this;
    var SEARCH_URL = API_URL + '/map/spots';
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

    vm.searchParams = {
      locations: [],
      tags: []
    };

    $rootScope.sortLayer = $rootScope.sortLayer || 'event';
    $rootScope.isDrawArea = false;
    $rootScope.toggleLayer = toggleLayer;

    $rootScope.$on('update-map-data', onUpdateMapData);

    run();

    ////////////////////////////

    function run() {
      loadCategories();
    }


    function onUpdateMapData(event, spots, layer, isDrawArea) {
      layer = layer || $rootScope.sortLayer;

      //group by spot type
      $rootScope.mapSortSpots = _.groupBy(spots, function (item) {
        return item.spot.category.type.name
      });

      if ($rootScope.mapSortSpots.event && $rootScope.mapSortSpots.event.length > 0) {
        $rootScope.mapSortSpots.event = _.map($rootScope.mapSortSpots.event, function (item) {
          SpotService.formatSpot(item.spot);
          return item;
        });
      }

      toggleLayer(layer, isDrawArea);
    }

    function toggleLayer(layer, isDrawArea) {
      if (angular.isDefined(isDrawArea)) {
        $rootScope.isDrawArea = isDrawArea;
      }
      console.log(isDrawArea);
      var wp = MapService.GetPathWaypoints();
      var geoJson = MapService.GetGeoJSON();

      $rootScope.sortLayer = layer;

      if (layer == 'weather') {
        MapService.showOtherLayers();
        MapService.WeatherSelection(weather);

        if (!vm.currentWeather) {
          toastr.info('Click on map to check weather in this area');
        }
      } else {
        MapService.showLayer(layer);

        MapService.drawSpotMarkers($rootScope.mapSortSpots[layer], layer, true);

        if (isDrawArea && wp.length < 1 && geoJson && geoJson.features.length < 1) {
          toastr.info('Draw the search area');
        }
      }
    }

    function onTagsAdd(q, w, e) {
      if (vm.searchParams.tags.length < restrictions.tags) {
        return true;
      } else {
        toastr.error('You can\'t add more than ' + restrictions.tags + ' tags');
        return false;
      }
    }

    function invalidTag(tag) {
      if (tag.name.length > 64) {
        toastr.error('Your tag is too long. Max 64 symbols.');
      } else {
        toastr.error('Invalid input.');
      }
    }

    function loadCategories() {
      if (!$rootScope.spotCategories) {
        $http.get(API_URL + '/spots/categories')
          .success(function (data) {
            $rootScope.spotCategories = data;
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
      console.log(vm.spotCategories);
    }


    function search() {
      var data = {
        search_text: vm.searchParams.search_text,
        filter: {
          tags: vm.searchParams.tags,
          rating: vm.searchParams.rating
        }
      };

      var bbox_array = MapService.GetBBoxes();
      if (bbox_array.length > 0) {
        bbox_array = MapService.BBoxToParams(bbox_array);
        data.filter.b_boxes = bbox_array;
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

      data = _.omit(data, _.isEmpty);
      $http.get(SEARCH_URL + '?' + jQuery.param(data))
        .success(function (spots) {
          console.log(spots);
          if (spots.length > 0) {
            onUpdateMapData(null, spots, $rootScope.sortLayer, bbox_array.length > 0);

            if (bbox_array.length == 0) {
              MapService.FitBoundsByLayer($rootScope.sortLayer);
            }
          } else {
            onUpdateMapData(null, [], null, bbox_array.length > 0);
          }
        }).catch(function (resp) {
          console.warn(resp);
          toastr.error('Search error');
        });
    }

    function selectAllCategories() {
      isSelectedAll = !isSelectedAll;
      _.each(vm.spotCategories[$rootScope.sortLayer], function (item) {
        item.selected = isSelectedAll;
      });
    }

    //============================ weather section =========================
    function weather(resp) {
      vm.vertical = false;
      vm.weatherForecast = [];
      var daily = resp.daily.data;

      for (var k in daily) {
        daily[k].formattedDate = moment(daily[k].time * 1000).format('DD MMMM');
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


