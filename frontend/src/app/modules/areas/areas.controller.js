(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('AreasController', AreasController);

  /** @ngInject */
  function AreasController(areas, Area, dialogs, toastr) {
    var vm = this;
    vm.areas = areas.slice();

    /*
     * Remove area
     * @param id {number} area id
     * @param idx {number} area index
     */
    vm.RemoveArea = function (id, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete?').result.then(function () {
        Area.delete({area_id: id}, function () {
          vm.areas.splice(idx, 1);
          toastr.info('Area successfully deleted');
        });
      });

    };
  }
})();
