(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SettingsController', SettingsController);

  /** @ngInject */
  function SettingsController(UploaderService) {
    var vm = this;
    vm.images = UploaderService.images;

    vm.save = function () {
      UploaderService.upload('http://api.zoomtivity/albums', {id: 1});
    }
  }
})();
