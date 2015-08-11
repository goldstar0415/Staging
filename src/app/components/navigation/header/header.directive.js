(function() {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('zmHeader', ZoomtivityHeader);

  /** @ngInject */
  function ZoomtivityHeader() {
    var directive = {
      restrict: 'E',
      templateUrl: 'app/components/navigation/header/header.html',
      scope: {
        options: "="
      },
      controller: HeaderController,
      controllerAs: 'Header',
      bindToController: true
    };

    return directive;

    /** @ngInject */
    function HeaderController($state) {
      var vm = this;
      vm.$state = $state;

      if(vm.options.snap.disable == "left") {
        vm.toggle = "right";
      } else {
        vm.toggle = "left";
      }
    }
  }

})();
