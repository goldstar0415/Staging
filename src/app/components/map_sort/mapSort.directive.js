(function () {
  'use strict';

  angular.module('zoomtivity')
    .directive('mapSort', function() {
      return {
        restrict: 'E',
        templateUrl: '/app/components/map_sort/map_sort.html',
        controller: mapSort,
        controllerAs: 'MapSort',
        scope: {

        }
      }
    });

  function mapSort($rootScope, $scope, MapService, $http, API_URL) {
    var vm = this;
    var originalSpotsArray = [];
    $scope.weatherForecast = [];

    $rootScope.$on('update-map-data', function(event, spots) {
      originalSpotsArray = spots;
      vm.eventsArray = [];
      vm.recreationsArray = [];
      vm.pitstopsArray = [];


      _.each(spots, function(item) {
        var type = item.spot.category.type.name;

        switch(type) {
          case 'pitstop':
            vm.pitstopsArray.push(item);
            break;
          case 'recreation':
            vm.recreationsArray.push(item);
            break;
          case 'event':
            vm.eventsArray.push(item);
            break;
        }

      });

      vm.eventsArray = MapService.SortByRating(vm.eventsArray);
      vm.recreationsArray = MapService.SortByRating(vm.recreationsArray);
      vm.pitstopsArray = MapService.SortByRating(vm.pitstopsArray);

      switch(vm.sortLayer) {
        case 'event':
          $scope.sortEventsByCategories();
          break;
        case 'recreation':
          $scope.sortRecreationsByCategories();
          break;
        case 'pitstop':
          $scope.sortPitstopsByCategories();
          break;
      }
    });


    vm.vertical = true;
    vm.sortLayer = 'event';
    vm.toggleMenu = function() {
      vm.vertical = !vm.vertical;
    };
    vm.toggleLayer = function(layer) {
      var wp = MapService.GetPathWaypoints();
      var geoJson = MapService.GetGeoJSON();


      switch(layer) {
        case 'events':
              MapService.showEvents();
              vm.sortLayer = 'event';
              $scope.sortEventsByCategories();
              if (wp.length < 1 && geoJson && geoJson.features.length < 1) {
                toastr.info('Draw the search area');
              }
              break;
        case 'pitstops':
              MapService.showPitstops();
              vm.sortLayer = 'pitstop';
              $scope.sortPitstopsByCategories();
              if (wp.length < 1 && geoJson && geoJson.features.length < 1) {
                toastr.info('Draw the search area');
              }
              break;
        case 'recreations':
              MapService.showRecreations();
              vm.sortLayer = 'recreation';
              $scope.sortRecreationsByCategories();
              if (wp.length < 1 && geoJson && geoJson.features.length < 1) {
                toastr.info('Draw the search area');
              }
              break;
        case 'other':
              MapService.showOtherLayers();
              MapService.WeatherSelection(weather);
              vm.sortLayer = 'weather';
              toastr.info('Click on map to check weather in this area');
              break;
      }
    };
    vm.toggleWeather = function() {
        vm.toggleLayer('other');
    };

    $http.get(API_URL+ '/spots/categories')
      .success(function(data) {
        for(var k in data) {
          data[k].selected = false;
          if(data[k].name == 'event'){
            $scope.eventCategories = data[k].categories;
          }

          if(data[k].name == 'recreation'){
            $scope.recreationCategories = data[k].categories;
          }

          if(data[k].name == 'pitstop'){
            $scope.pitstopCategories = data[k].categories;
          }
        }
      });

    //============================ events section ==========================
    $scope.eventsDateToggle = false;
    $scope.eventsCategoryToggle = false;
    vm.eventsSelectAll = false;

    $scope.checkItem = function(item, items, type) {
      if(!item.selected) {
        switch(type) {
          case 'event':
            vm.eventsSelectAll = false;
            break;
          case 'recreation':
            vm.recreationSelectAll = false;
            break;
          case 'pitstop':
            vm.pitstopSelectAll = false;
            break;
        }
      } else {
        switch(type) {
          case 'event':
            vm.eventsSelectAll = checkAll(items);
            break;
          case 'recreation':
            vm.recreationSelectAll = checkAll(items);
            break;
          case 'pitstop':
            vm.pitstopSelectAll = checkAll(items);
            break;
        }
      }

      function checkAll(items) {
        var selected = true;
        for(var k in items) {
          if(items[k].selected == false) {
            selected = false;
            break;
          }
        }

        return selected;
      }
    };

    $scope.eventsSortByDate = function() {
      if(vm.startDate || vm.endDate) {
        vm.displayEventsArray = MapService.ClampByDate(vm.eventsArray, vm.startDate, vm.endDate);
        MapService.drawSpotMarkers(vm.displayEventsArray, 'event', true);
        console.log(vm.displayEventsArray);
      }
    };

    $scope.toggleEventCategories = function() {
      if(!vm.eventsSelectAll) {
        _.map($scope.eventCategories, function(item) {
          item.selected = true;
        });
      } else {
       _.map($scope.eventCategories, function(item) {
          item.selected = false;
        });
      }
      vm.eventsSelectAll = !vm.eventsSelectAll;
      $scope.sortEventsByCategories();
    };
    $scope.sortEventsByCategories = function() {
      var categories = _.reject($scope.eventCategories, function(item) {
        return !item.selected;
      });
      vm.displayEventsArray = MapService.SortBySubcategory(vm.eventsArray, categories);
      MapService.drawSpotMarkers(vm.displayEventsArray, 'event', true);
    };
    //============================ recreation section ======================
    $scope.recreationsCategoryToggle = false;
    vm.recreationSelectAll = false;

    $scope.toggleRecreationCategories = function() {
      if(!vm.recreationSelectAll) {
        _.map($scope.recreationCategories, function(item) {
          item.selected = true;
        });
      } else {
        _.map($scope.recreationCategories , function(item) {
          item.selected = false;
        });
      }
      vm.recreationSelectAll = !vm.recreationSelectAll;
      $scope.sortRecreationsByCategories();
    };
    $scope.sortRecreationsByCategories = function() {
      var categories = _.reject($scope.recreationCategories, function(item) {
        return !item.selected;
      });
      vm.displayRrecreationsArray = MapService.SortBySubcategory(vm.recreationsArray, categories);
      MapService.drawSpotMarkers(vm.displayRrecreationsArray, 'recreation', true);
    };
    //============================ pitstop section =========================
    $scope.pitstopsCategoryToggle = false;
    vm.pitstopSelectAll = false;

    $scope.togglePitstopCategories = function() {
      if(!vm.pitstopSelectAll) {
        _.map($scope.pitstopCategories, function(item) {
          item.selected = true;
        });
      } else {
        _.map($scope.pitstopCategories, function(item) {
          item.selected = false;
        });
      }
      vm.pitstopSelectAll = !vm.pitstopSelectAll;
      $scope.sortPitstopsByCategories();
    };
    $scope.sortPitstopsByCategories = function() {
      var categories = _.reject($scope.pitstopCategories, function(item) {
        return !item.selected;
      });
      vm.displayPitstopsArray = MapService.SortBySubcategory(vm.pitstopsArray, categories);
      MapService.drawSpotMarkers(vm.displayPitstopsArray, 'pitstop', true);
    };
    //============================ weather section =========================


    function weather(resp) {
      vm.vertical = false;
      $scope.weatherForecast = [];
      var daily = resp.daily.data;
      console.log(daily);
      for(var k in daily) {
        daily[k].formattedDate = moment(daily[k].time * 1000).format('DD MMMM');
        if(k != 0) {
          $scope.weatherForecast.push(daily[k]);
        }
      }
      $scope.currentWeather = daily[0];
      $scope.currentWeather.sunrise = moment(daily[0].sunriseTime * 1000).format('HH:mm a');
      $scope.currentWeather.sunset = moment(daily[0].sunsetTime * 1000).format('HH:mm a');
      $scope.currentWeather.temperature = ((daily[0].temperatureMax + daily[0].temperatureMin) / 2).toFixed(2);

    }
  }
})();


