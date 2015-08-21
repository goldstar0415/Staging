(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('AreasController', AreasController);

  /** @ngInject */
  function AreasController(areas) {
    var vm = this;
    vm.areas = areas;

  }
})();
