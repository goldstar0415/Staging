(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('CropService', CropService);

  /** @ngInject */
  function CropService($modal) {
    function CropImage(image, outputWidth, outputHeight, callback) {
      var modalInstance = $modal.open({
        animation: true,
        templateUrl: '/app/components/crop/crop.html',
        controller: CropModalController,
        controllerAs: 'Crop',
        modalClass: 'modalFix crop-modal',
        modalContentClass: 'clearfix',
        backdrop: 'static',
        resolve: {
          image: function () {
            return image
          },
          width: function () {
            return outputWidth
          },
          height: function () {
            return outputHeight
          }
        }
      });

      modalInstance.result.then(function (CroppedImage) {
        callback(CroppedImage);
      }, function () {
        callback(null);
      });
    }

    return {
      crop: CropImage
    }
  }

  function CropModalController($modalInstance, $scope, image, width, height) {
    var vm = this;
    vm.width = width || 512;
    vm.height = height || 512;
    vm.image = '';
    vm.resultImage = '';

    if (typeof image == 'string') {
      vm.image = image;
    } else {
      var reader = new FileReader();
      reader.onload = function (evt) {
        vm.image = evt.target.result;
      };
      reader.readAsDataURL(image);
    }


    vm.save = function () {
      $modalInstance.close(vm.resultImage);
    };

    vm.cancel = function () {
      $modalInstance.dismiss('cancel');
    };
  }

})();
