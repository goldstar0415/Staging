(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('imageCropper', function ($timeout, $rootScope) {
      return {
        restrict: 'E',
        template: '<img id="imageCrop" style="width: 100%"/>',
        replace: true,
        scope: {
          sourceImage: '=',
          resultImage: '=',
          cropHeight: '=',
          cropWidth: '=',
          autoCrop: '='
        },
        link: function (scope, elem, attrs) {
          var ratioWidth = scope.cropWidth || 16;
          var ratioHeight = scope.cropHeight || 9;
          var autoCrop = scope.autoCrop || false;
          var $image = $('#imageCrop');

          $image.cropper({
            aspectRatio: ratioWidth / ratioHeight,
            cropmove: onCropChange,
            cropend: onCropEnd,
            autoCrop: true,
            strict: true
          });

          //crop image after it to be loaded
          $image.on('built.cropper', onCropEnd);

          scope.$watch('sourceImage', function (newValue, oldValue) {
            if (newValue) {
              $image.cropper('replace', newValue);
            }
          });

          function onCropChange() {
            if (autoCrop) {
              onCropEnd();
            }
          }

          //apply crop
          function onCropEnd() {
            scope.resultImage = getDataURL();
            scope.$apply();
          }

          //get cropped image as BASE64
          function getDataURL() {
            return $image.cropper('getCroppedCanvas').toDataURL()
          }
        }
      }
    });
})();
