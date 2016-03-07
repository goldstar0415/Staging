(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('IntroController', IntroController);

  /** @ngInject */
  function IntroController($state, $rootScope, toastr, $http, API_URL, DATE_FORMAT) {
    var vm = this;
    var SEARCH_URL = API_URL + '/map/spots';

    vm.searchParams = {
      filter: {}
    };
    vm.ratings = [];

    vm.searchEvent = searchEvent;
    vm.searchGrub = searchGrub;
    vm.searchTodo = searchTodo;
    vm.searchRoom = searchRoom;
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


    function doSearch(params) {
      var promise = $http.get(SEARCH_URL, {
        params: params
      });

      promise.catch(function (resp) {
        console.log(resp);
        toastr.error('Search error');
      });

      return promise;
    }



    function routeSearch() {
      $state.go('index', {pathSelection: true, spotType: 'event'});
    }

    function radiusSearch() {
      $state.go('index', {radiusSelection: true, activeSpotType: 'event'});
    }

    function goSignIn() {
      $state.go('index', {openSignIn: !$rootScope.currentUser});
    }
  }
})();
