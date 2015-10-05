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

      $scope.$watch('Stars.item.rating', function (value, old) {
        if (value != old && $rootScope.currentUser && !vm.item.is_rated) {
          Spot.rate({id: vm.item.id}, {vote: parseInt(value)});
          vm.item.is_rated = true;
        }
      });
    }
  }
})();
