(function () {
  'use strict';

  angular.module('zoomtivity')
    .directive('mapSort', function() {
      return {
        restrict: 'E',
        templateUrl: 'app/components/map_sort/map_sort.html',
        controller: mapSort,
        controllerAs: 'MapSort',
        scope: {

        }
      }
    });

  function mapSort($scope, MapService) {
    var vm = this;

    vm.vertical = false;
    vm.toggleMenu = function() {
      vm.vertical = !vm.vertical;
    };

  }
})();


