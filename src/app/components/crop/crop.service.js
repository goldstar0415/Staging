(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('CropService', CropService);

  /** @ngInject */
  function CropService($modal) {
    function CropImage(image, callback) {
      var modalInstance = $modal.open({
        animation: true,
        templateUrl: 'app/components/crop/crop.html',
        controller: CropModalController,
        controllerAs: 'Crop',
        modalClass: 'modalFix',
        backdrop: 'static',
        resolve: {
          image: function () {
            return image;
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

  function CropModalController($modalInstance, $scope, image) {
    var vm = this;
    vm.image = '';
    vm.resultImage = '';

    if(typeof image == 'string') {
      vm.image = image;
    } else {
      var reader = new FileReader();
      reader.onload = function (evt) {
        vm.image = evt.target.result;
      };
      reader.readAsDataURL(image);
    }


    vm.save = function() {
      $modalInstance.close(vm.resultImage);
    };

    vm.cancel = function() {
      $modalInstance.dismiss('cancel');
    };
  }

})();
