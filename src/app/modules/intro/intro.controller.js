(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('IntroController', IntroController);

  /** @ngInject */
  function IntroController($state, $rootScope, toastr, $http, MapService, API_URL, DATE_FORMAT) {
    var vm = this;
    var SEARCH_URL = API_URL + '/map/spots';
    var geocoder = new google.maps.Geocoder();

    vm.searchParams = {
      filter: {},
      addresses: []
    };

    vm.locations = [];
    vm.location = {};

    vm.searchEvent = searchEvent;
    vm.searchGrub = searchGrub;
    vm.searchTodo = searchTodo;
    vm.searchRoom = searchRoom;
    vm.searchAddress = searchAddress;
    vm.selectLocation = selectLocation;
    vm.searchRoute = searchRoute;
    vm.addLocation = addLocation;
    vm.removeLocation = removeLocation;
    vm.routeSearch = routeSearch;
    vm.radiusSearch = radiusSearch;
    vm.goSignIn = goSignIn;

    run();

    function run() {

    }

    function selectLocation(location) {
      vm.location = {
        latitude: location.geometry.location.lat(),
        longitude: location.geometry.location.lng(),
        address: location.formatted_address
      };
    }

    function searchAddress() {
      vm.locations = [];
      geocoder.geocode({address: vm.searchParams.search_text}, function(results) {
        vm.locations = results;
      });
    }

    function searchEvent() {
      if (vm.searchParams.search_text || vm.searchParams.filter.start_date || vm.searchParams.filter.end_date) {
        var data = {
          type: 'event',
          search_text: vm.searchParams.search_text,
          location: vm.location,
          filter: {}
        };
        if (vm.searchParams.filter.start_date) {
          data.filter.start_date = moment(vm.searchParams.filter.start_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
        }
        if (vm.searchParams.filter.end_date) {
          data.filter.end_date = moment(vm.searchParams.filter.end_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
        }
        MapService.FocusMapToGivenLocation({latitude: 0, longitude: 0, zoom: 20});
        doSearch(data);
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

        doSearch(data);
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

        doSearch(data);
      }
    }

    function searchRoom() {
      if (vm.searchParams.search_text || vm.searchParams.category_airbnb || vm.searchParams.category_hotel) {

        var data = {
          type: 'shelter',
          search_text: vm.searchParams.search_text,
          filter: {
            category_ids: []
          }
        };

        if ($rootScope.spotCategories) {
          var shelterCategory = _.findWhere($rootScope.spotCategories, {name: 'shelter'});

          if (vm.searchParams.category_hotels) {
            _addCategory(shelterCategory, data.filter, 'hotels');
          }
          if (vm.searchParams.category_homes) {
            _addCategory(shelterCategory, data.filter, 'homes-condos');
          }
          if (vm.searchParams.category_campground) {
            _addCategory(shelterCategory, data.filter, 'cabins-campgrounds');
          }
        }


        doSearch(data);
      }
    }

    function _addCategory(shelterCategory, filter, name) {
      if (shelterCategory) {
        var category = _.findWhere(shelterCategory.categories, {name: name});
        if (category) {
          console.log(category, name);
          filter.category_ids.push(category.id);
        }
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
        $state.go('index', {spotSearch: {roadSelection: selection}});
      }
    }


    function doSearch(params) {
      var promise = $http.get(SEARCH_URL + '?' + jQuery.param(params));

      promise.success(function (spots) {
        $state.go('index', {
          spotSearch: {spots: spots, activeSpotType: params.type},
          spotLocation: params.location
        });
      });

      promise.catch(function (resp) {
        console.log(resp);
        toastr.error('Search error');
      });

      return promise;
    }


    function routeSearch() {
      $state.go('index', {spotSearch: {pathSelection: true, activeSpotType: 'event'}});
    }

    function radiusSearch() {
      $state.go('index', {spotSearch: {radiusSelection: true, activeSpotType: 'event'}});
    }

    function goSignIn() {
      $state.go('index', {spotSearch: {openSignIn: !$rootScope.currentUser}});
    }
  }
})();
