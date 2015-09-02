(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('AreasController', AreasController);

  /** @ngInject */
  function AreasController(areas, Area, dialogs, toastr) {
    var vm = this;
    vm.areas = areas.slice();

    vm.RemoveArea = function(id, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete area?').result.then(function () {
        Area.delete({area_id: id}, function() {
          vm.areas.splice(idx, 1);
          toastr.info('Area successfully deleted');
        });
      });

    };
  }
})();
