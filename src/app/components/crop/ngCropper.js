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

          $image.on('built.cropper', function () {
            onCropEnd();
          });
          //$timeout(function () {
          //  scope.resultImage = getDataURL();
          //});

          scope.$watch('sourceImage', function (newValue, oldValue) {
            console.log(newValue);
            if (newValue) {
              $image.cropper('replace', newValue);
            }
          });

          function onCropChange() {
            if (autoCrop) {
              onCropEnd();
            }
          }

          function onCropEnd() {
            scope.resultImage = getDataURL();
            scope.$apply();
          }

          function getDataURL() {
            return $image.cropper('getCroppedCanvas').toDataURL()
          }
        }
      }
    });
})();
