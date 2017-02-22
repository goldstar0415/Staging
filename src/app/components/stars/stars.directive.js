(function () {
  'use strict';

  /*
   * Directive for spot rating
   */
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
    function StarsController($scope, Spot, $rootScope) {
      var vm = this;
      vm.avg_rating = vm.item.avg_rating;
      $scope.$watch('Stars.item.avg_rating', function (value, old) {
        vm.avg_rating = value;
      });
    }
  }
})();
