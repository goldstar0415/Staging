(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('MySpotsController', MySpotsController);

  /** @ngInject */
  function MySpotsController($http, API_URL, spots, moment, toastr) {
    var vm = this;
    vm.spots = spots.data;
    formatSpots();


    vm.removeSpot = function(spot_id) {
      $http.delete(API_URL + '/spots/' + spot_id).then(
        function success(response) {
          if(response.data.result){
            for(var k in vm.spots) {
              if(vm.spots[k].id == spot_id) {
                vm.spots.splice(k, 1);
                break;
              }
            }
          }
        },
        function error() {
          toastr.error('Oops. Something went wrong. Please, try again later.')
        }
      )
    };
    function formatSpots() {
      for(var k in vm.spots) {
        vm.spots[k].type = vm.spots[k].category.type.display_name;
        vm.spots[k].start_time = moment(vm.spots[k].start_date).format('hh:mm a');
        vm.spots[k].end_time = moment(vm.spots[k].end_date).format('hh:mm a');
        vm.spots[k].start_date = moment(vm.spots[k].start_date).format('YYYY-MM-DD');
        vm.spots[k].end_date = moment(vm.spots[k].end_date).format('YYYY-MM-DD');
      }
    }
  }
})();
