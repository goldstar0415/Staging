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
    function HeaderController(moment) {
      var vm = this;

      if(vm.options.snap.disable == "left") {
        vm.toggle = "right";
      } else {
        vm.toggle = "left";
      }
    }
  }

})();
