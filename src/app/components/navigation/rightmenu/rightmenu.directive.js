(function() {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('zmRightmenu', ZoomtivityRightMenu);

  /** @ngInject */
  function ZoomtivityRightMenu() {
    var directive = {
      restrict: 'E',
      templateUrl: 'app/components/navigation/rightmenu/rightmenu.html',
      scope: {},
      controller: RightmenuController,
      controllerAs: 'Rightmenu',
      bindToController: true
    };

    return directive;

    /** @ngInject */
    function RightmenuController(moment) {
      var vm = this;

    }
  }

})();
