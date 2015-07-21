(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('uploader', uploader);

  /** @ngInject */
  function uploader() {
    return {
      restrict: 'E',
      templateUrl: 'app/components/uploader/uploader.html',
      controller: UploaderController,
      controllerAs: 'Uploader',
      bindToController: true
    };

    /** @ngInject */
    function UploaderController(UploaderService) {
      var vm = this;
      vm.images = UploaderService.images;

    }


  }

})();
