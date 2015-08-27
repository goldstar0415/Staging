(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('AreasController', AreasController);

  /** @ngInject */
  function AreasController(areas, $rootScope) {
    var vm = this;
    vm.areas = areas;
    console.log(areas);

  }
})();
