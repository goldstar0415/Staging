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

    vm.vertical = true;
    vm.weatherForecast = [];
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;

    $rootScope.mapSortSpots = $rootScope.mapSortSpots || {};
    $rootScope.sortLayer = $rootScope.sortLayer || 'event';
    $rootScope.toggleLayer = toggleLayer;

    $rootScope.$on('update-map-data', onUpdateMapData);

    run();

    ////////////////////////////

    function run() {
      loadCategories();
    }


    function onUpdateMapData(event, spots, layer, isDrawArea) {
      console.log(arguments);
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
      isDrawArea = _.isUndefined(isDrawArea) ? true : isDrawArea;
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
      vm.spotCategories = _.map(data, function (item) {
        var category = {};
        category[item.name] = item.categories;
        return category;
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


