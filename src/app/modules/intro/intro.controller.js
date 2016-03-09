(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('IntroController', IntroController);

  /** @ngInject */
  function IntroController($state, $rootScope, toastr, $http, MapService, API_URL, DATE_FORMAT) {
    var vm = this;
    var SEARCH_URL = API_URL + '/map/spots';

    vm.searchParams = {
      filter: {},
      addresses: []
    };
    vm.locations = [];

    vm.searchEvent = searchEvent;
    vm.searchGrub = searchGrub;
    vm.searchTodo = searchTodo;
    vm.searchRoom = searchRoom;
    vm.searchRoute = searchRoute;
    vm.addLocation = addLocation;
    vm.removeLocation = removeLocation;
    vm.routeSearch = routeSearch;
    vm.radiusSearch = radiusSearch;
    vm.goSignIn = goSignIn;

    run();

    /////

    function run() {

    }

    function searchEvent() {
      if (vm.searchParams.search_text || vm.searchParams.filter.start_date || vm.searchParams.filter.end_date) {
        var data = {
          type: 'event',
          search_text: vm.searchParams.search_text,
          filter: {}
        };
        if (vm.searchParams.filter.start_date) {
          data.filter.start_date = moment(vm.searchParams.filter.start_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
        }
        if (vm.searchParams.filter.end_date) {
          data.filter.end_date = moment(vm.searchParams.filter.end_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
        }

        doSearch(data)
          .success(function (data) {
            console.log(data);
          });
      }
    }

    function searchGrub() {
      if (vm.searchParams.search_text || vm.searchParams.rating) {

        var data = {
          type: 'food',
          search_text: vm.searchParams.search_text,
          filter: {
            rating: vm.searchParams.rating
          }
        };

        doSearch(data)
          .success(function (data) {
            console.log(data);
          })
        ;
      }
    }

    function searchTodo() {
      if (vm.searchParams.search_text || vm.searchParams.rating) {

        var data = {
          type: 'todo',
          search_text: vm.searchParams.search_text,
          filter: {
            rating: vm.searchParams.rating
          }
        };

        doSearch(data)
          .success(function (data) {
            console.log(data);
          })
        ;
      }
    }

    function searchRoom() {
      if (vm.searchParams.search_text || vm.searchParams.category_airbnb || vm.searchParams.category_hotel) {

        var data = {
          type: 'food',
          search_text: vm.searchParams.search_text,
          filter: {
            category_ids: []
          }
        };
        var category;

        if (vm.searchParams.category_airbnb && $rootScope.spotCategories) {
          category = _.find($rootScope.spotCategories[3].categories, {name: 'air_bnb'});
          data.filter.category_ids.push(category.id);
        }
        if (vm.searchParams.category_hotel && $rootScope.spotCategories) {
          category = _.find($rootScope.spotCategories[3].categories, {name: 'hotel'});
          data.filter.category_ids.push(category.id);
        }

        doSearch(data)
          .success(function (data) {
            console.log(data);
          })
        ;
      }
    }

    function addLocation() {
      if (vm.newLocation && vm.newLocation.address && vm.newLocation.location) {
        var item = angular.copy(vm.newLocation);
        vm.locations.unshift(item);
        vm.newLocation = {};
      } else {
        toastr.error('Wrong location');
        vm.newLocation = {};
      }
    }

    function removeLocation(idx) {
      vm.locations.splice(idx, 1);
    }

    function searchRoute() {
      var points = [];

      if (vm.newLocation && vm.newLocation.location) {
        points.push(vm.newLocation.location);
      }
      if (vm.locations.length > 0) {
        points = _.union(points, _.pluck(vm.locations, 'location'));
      }

      if (points.length > 0) {
        var selection = {
          data: {
            type: "FeatureCollection",
            features: []
          },
          waypoints: [points]
        };
        $state.go('index', {roadSelection: selection});
      }
    }


    function doSearch(params) {
      var promise = $http.get(SEARCH_URL + '?' + $.param(params));

      promise.catch(function (resp) {
        console.log(resp);
        toastr.error('Search error');
      });

      return promise;
    }


    function routeSearch() {
      $state.go('index', {pathSelection: true, activeSpotType: 'event'});
    }

    function radiusSearch() {
      $state.go('index', {radiusSelection: true, activeSpotType: 'event'});
    }

    function goSignIn() {
      $state.go('index', {openSignIn: !$rootScope.currentUser});
    }
  }
})();
