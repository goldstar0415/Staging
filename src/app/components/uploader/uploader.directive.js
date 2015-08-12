(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('uploader', uploader);

  /** @ngInject */
  function uploader() {
    return {
      restrict: 'E',
      scope: {
        dropZone: '='
      },
      templateUrl: '/app/components/uploader/uploader.html',
      controller: UploaderController,
      controllerAs: 'Uploader',
      bindToController: true
    };

    /** @ngInject */
    function UploaderController($scope, UploaderService) {
      var vm = this;
      vm.images = UploaderService.images.files;

      $scope.$watch('Uploader.images', function (val) {
        if(val) {
          UploaderService.images.files = val;
        }
      });
    }
  }

})();
