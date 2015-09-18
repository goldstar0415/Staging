(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('imageCropper', function () {
      return {
        restrict: 'E',
        template: '<img id="imageCrop" ng-src="{{sourceImage}}" style="width: 100%"/>',
        replace: true,
        scope: {
          sourceImage: '=',
          resultImage: '=',
          cropHeight: '=',
          cropWidth: '=',
          autoCrop: '='
        },
        link: function (scope, elem, attrs) {
          var $image = $('#imageCrop');
          var ratioWidth = scope.cropWidth || 16;
          var ratioHeight = scope.cropHeight || 9;
          var autoCrop = scope.autoCrop || false;

          //scope.$watch('sourceImage', function (v, n) {
          //  if (v) {
          //    $image.bind('load', function () {
          //      $image.on('build.cropper', function () {
          //        onCropEnd();
          //      });
          //    });
          //  }
          //});

          $image.on('load', function () {
            console.log(1);
            $image.cropper({
              aspectRatio: ratioWidth / ratioHeight,
              cropmove: onCropChange,
              cropend: onCropEnd,
              strict: true
            });

            $image.on('build.cropper', function () {
              onCropEnd();
            });
            onCropEnd();

          });

          function onCropChange() {
            if (autoCrop) {
              onCropEnd();
            }
          }

          function onCropEnd() {
            console.log('onCropEnd');
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
