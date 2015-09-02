(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('AreasController', AreasController);

  /** @ngInject */
  function AreasController(areas, Area) {
    var vm = this;
    vm.areas = areas;

    vm.RemoveArea = function(id, idx) {
      Area.delete({area_id: id}, function() {
        vm.areas.splice(idx, 1);
      });
    };
  }
})();
