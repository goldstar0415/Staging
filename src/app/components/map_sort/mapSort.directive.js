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

  function mapSort($rootScope, $scope, MapService, $http, $state, SpotService, API_URL, DATE_FORMAT) {
    var vm = this;
    var originalSpotsArray = [];
    vm.weatherForecast = [];
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;

    vm.vertical = true;
    $rootScope.sortLayer = $rootScope.sortLayer || 'event';

    $rootScope.toggleLayer = toggleLayer;
    vm.toggleMenu = function () {
      vm.vertical = !vm.vertical;
    };
    vm.toggleWeather = function () {
      toggleLayer('other');
    };

    $rootScope.$on('update-map-data', onUpdateMapData);

    //============================ events section ==========================
    vm.eventsCategoryToggle = false;
    vm.eventsSelectAll = false;

    vm.checkItem = checkItem;

    //TODO: DELETE THIS SHIT
    vm.eventsSortByDate = eventsSortByDate;
    vm.toggleEventCategories = toggleEventCategories;
    vm.sortEventsByCategories = sortEventsByCategories;

    //============================ todos section ======================
    vm.todoCategoryToggle = false;
    vm.todoSelectAll = false;

    vm.toggleTodoCategories = toggleTodoCategories;
    vm.sortTodoByCategories = sortTodoByCategories;
    //============================ food section =========================
    vm.foodCategoryToggle = false;
    vm.foodSelectAll = false;

    vm.toggleFoodCategories = toggleFoodCategories;
    vm.sortFoodByCategories = sortFoodByCategories;
    //============================ shelter section =========================
    vm.shelterCategoryToggle = false;
    vm.shelterSelectAll = false;

    vm.toggleShelterCategories = toggleShelterCategories;
    vm.sortShelterByCategories = sortShelterByCategories;

    loadCategories();

    function onUpdateMapData(event, spots) {
      originalSpotsArray = spots;
      vm.eventsArray = [];
      vm.todoArray = [];
      vm.foodArray = [];
      vm.shelterArray = [];

      _.each(spots, function (item) {
        var type = item.spot.category.type.name;

        switch (type) {
          case 'food':
            vm.foodArray.push(item);
            break;
          case 'shelter':
            vm.shelterArray.push(item);
            break;
          case 'todo':
            vm.todoArray.push(item);
            break;
          case 'event':
            SpotService.formatSpot(item.spot);
            vm.eventsArray.push(item);
            break;
        }
      });

      vm.eventsArray = MapService.SortByRating(vm.eventsArray);
      vm.todoArray = MapService.SortByRating(vm.todoArray);
      vm.foodArray = MapService.SortByRating(vm.foodArray);
      vm.shelterArray = MapService.SortByRating(vm.shelterArray);

      switch ($rootScope.sortLayer) {
        case 'event':
          vm.sortEventsByCategories();
          break;
        case 'todo':
          vm.sortTodoByCategories();
          break;
        case 'food':
          vm.sortFoodByCategories();
          break;
        case 'shelter':
          vm.sortShelterByCategories();
          break;
      }
    }

    function toggleLayer(layer) {
      var wp = MapService.GetPathWaypoints();
      var geoJson = MapService.GetGeoJSON();

      switch (layer) {
        case 'events':
          MapService.showEvents();
          $rootScope.sortLayer = 'event';
          vm.sortEventsByCategories();
          if ( wp.length < 1 && geoJson && geoJson.features.length < 1) {
            toastr.info('Draw the search area');
          }
          break;
        case 'food':
          MapService.showFood();
          $rootScope.sortLayer = 'food';
          vm.sortFoodByCategories();
          if ( wp.length < 1 && geoJson && geoJson.features.length < 1) {
            toastr.info('Draw the search area');
          }
          break;
        case 'shelter':
          MapService.showShelter();
          $rootScope.sortLayer = 'shelter';
          vm.sortShelterByCategories();
          if ( wp.length < 1 && geoJson && geoJson.features.length < 1) {
            toastr.info('Draw the search area');
          }
          break;
        case 'todo':
          MapService.showTodo();
          $rootScope.sortLayer = 'todo';
          vm.sortTodoByCategories();
          if (wp.length < 1 && geoJson && geoJson.features.length < 1) {
            toastr.info('Draw the search area');
          }
          break;
        case 'other':
          MapService.showOtherLayers();
          MapService.WeatherSelection(weather);
          $rootScope.sortLayer = 'weather';
          toastr.info('Click on map to check weather in this area');
          break;
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
      for (var k in data) {
        if (data[k].name == 'event') {
          vm.eventCategories = data[k].categories;
          for (var i in vm.eventCategories) {
            vm.eventCategories[i].selected = false;
          }
        }

        if (data[k].name == 'todo') {
          vm.todoCategories = data[k].categories;
          for (var i in vm.todoCategories) {
            vm.todoCategories[i].selected = false;
          }
        }

        if (data[k].name == 'food') {
          vm.foodCategories = data[k].categories;
          for (var i in vm.foodCategories) {
            vm.foodCategories[i].selected = false;
          }
        }

        if (data[k].name == 'shelter') {
          vm.shelterCategories = data[k].categories;
          for (var i in vm.shelterCategories) {
            vm.shelterCategories[i].selected = false;
          }
        }
      }
    }

    function checkItem(item, items, type) {
      item.selected = !item.selected;
      if (!item.selected) {
        switch (type) {
          case 'event':
            vm.eventsSelectAll = false;
            break;
          case 'todo':
            vm.todoSelectAll = false;
            break;
          case 'food':
            vm.foodSelectAll = false;
            break;
          case 'shelter':
            vm.shelterSelectAll = false;
            break;
        }
      } else {
        switch (type) {
          case 'event':
            vm.eventsSelectAll = checkAll(items);
            break;
          case 'todo':
            vm.todoSelectAll = checkAll(items);
            break;
          case 'food':
            vm.foodSelectAll = checkAll(items);
            break;
          case 'shelter':
            vm.shelterSelectAll = checkAll(items);
            break;
        }
      }

      function checkAll(items) {
        var selected = true;
        for (var k in items) {
          if (!items[k].selected) {
            selected = false;
            break;
          }
        }

        return selected;
      }
    }

    function eventsSortByDate() {
      if (vm.startDate || vm.endDate) {
        vm.displayEventsArray = MapService.ClampByDate(vm.eventsArray, vm.startDate, vm.endDate);
        MapService.drawSpotMarkers(vm.displayEventsArray, 'event', true);
      }
    }

    function toggleEventCategories() {
      if (!vm.eventsSelectAll) {
        _.map(vm.eventCategories, function (item) {
          item.selected = true;
        });
      } else {
        _.map(vm.eventCategories, function (item) {
          item.selected = false;
        });
      }
      vm.eventsSelectAll = !vm.eventsSelectAll;
      vm.sortEventsByCategories();
    }

    function sortEventsByCategories() {
      var categories = _.reject(vm.eventCategories, function (item) {
        return !item.selected;
      });
      vm.displayEventsArray = MapService.SortBySubcategory(vm.eventsArray, categories);
      MapService.drawSpotMarkers(vm.displayEventsArray, 'event', true);
    }

    function toggleTodoCategories() {
      if (!vm.todoSelectAll) {
        _.map(vm.todoCategories, function (item) {
          item.selected = true;
        });
      } else {
        _.map(vm.todoCategories, function (item) {
          item.selected = false;
        });
      }
      vm.todoSelectAll = !vm.todoSelectAll;
      vm.sortTodoByCategories();
    }

    function sortTodoByCategories() {
      var categories = _.reject(vm.todoCategories, function (item) {
        return !item.selected;
      });
      vm.displayTodoArray = MapService.SortBySubcategory(vm.todoArray, categories);
      MapService.drawSpotMarkers(vm.displayTodoArray, 'todo', true);
    }

    function toggleFoodCategories() {
      if (!vm.foodSelectAll) {
        _.map(vm.foodCategories, function (item) {
          item.selected = true;
        });
      } else {
        _.map(vm.foodCategories, function (item) {
          item.selected = false;
        });
      }
      vm.foodSelectAll = !vm.foodSelectAll;
      vm.sortFoodByCategories();
    }

    function sortFoodByCategories() {
      var categories = _.reject(vm.foodCategories, function (item) {
        return !item.selected;
      });
      vm.displayFoodArray = MapService.SortBySubcategory(vm.foodArray, categories);
      MapService.drawSpotMarkers(vm.displayFoodArray, 'food', true);
    }

    function toggleShelterCategories() {
      if (!vm.shelterSelectAll) {
        _.map(vm.shelterCategories, function (item) {
          item.selected = true;
        });
      } else {
        _.map(vm.shelterCategories, function (item) {
          item.selected = false;
        });
      }
      vm.shelterSelectAll = !vm.shelterSelectAll;
      vm.sortShelterByCategories();
    }

    function sortShelterByCategories() {
      var categories = _.reject(vm.shelterCategories, function (item) {
        return !item.selected;
      });
      vm.displayShelterArray = MapService.SortBySubcategory(vm.shelterArray, categories);
      MapService.drawSpotMarkers(vm.displayShelterArray, 'shelter', true);
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


