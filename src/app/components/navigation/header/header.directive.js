(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('zmHeader', ZoomtivityHeader);

  /** @ngInject */
  function ZoomtivityHeader() {
    var directive = {
      restrict: 'E',
      templateUrl: '/app/components/navigation/header/header.html',
      scope: {
        options: "="
      },
      controller: HeaderController,
      controllerAs: 'Header',
      bindToController: true
    };

    return directive;

    /** @ngInject */
    function HeaderController($state, $rootScope, API_URL) {
      var vm = this;
      vm.$state = $state;
      vm.API_URL = API_URL;

      if (vm.options.snap.disable == "left") {
        vm.toggle = "right";
      } else {
        vm.toggle = "left";
      }

    }
  }

})();
