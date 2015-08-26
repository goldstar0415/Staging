(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('stars', Stars);

  /** @ngInject */
  function Stars() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/stars/stars.html',
      scope: {
        item: '='
      },
      controller: StarsController,
      controllerAs: 'Stars',
      bindToController: true
    };

    /** @ngInject */
    function StarsController($scope, Spot) {
      var vm = this;

      $scope.$watch('Stars.item.rating', function (value, old) {
        if (value != old) {
          console.log(vm.item.rating);
          Spot.rate({id: vm.item.id}, {vote: parseInt(value)});
        }
      });
    }
  }
})();
