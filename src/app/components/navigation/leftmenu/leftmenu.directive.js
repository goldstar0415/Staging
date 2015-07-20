(function() {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('zmLeftmenu', ZoomtivityLeftMenu);

  /** @ngInject */
  function ZoomtivityLeftMenu() {
    var directive = {
      restrict: 'E',
      templateUrl: 'app/components/navigation/leftmenu/leftmenu.html',
      scope: {},
      controller: LeftmenuController,
      controllerAs: 'Leftmenu',
      bindToController: true
    };

    return directive;

    /** @ngInject */
    function LeftmenuController(moment) {
      var vm = this;

    }
  }

})();
