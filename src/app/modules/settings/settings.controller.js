(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SettingsController', SettingsController);

  /** @ngInject */
  function SettingsController(UploaderService, toastr, API_URL) {
    var vm = this;
    vm.images = UploaderService.images;

    vm.deleteImage = function (idx) {
      vm.images.data.splice(idx, 1);
    };
    vm.save = function () {
      UploaderService
        .upload(API_URL + '/albums', 'POST', {id: 1})
        .then(function (resp) {
          console.log(resp);
        })
        .catch(function (resp) {
          toastr.error('Upload failed');
        })
      ;
    }
  }
})();
