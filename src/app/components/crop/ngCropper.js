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


          $image.bind('load', function() {
            $image.cropper({
              aspectRatio: ratioWidth / ratioHeight,
              cropmove: onCropChange,
              cropend: onCropEnd,
              strict: true
            });

            $image.on('built.cropper', function() {
              onCropEnd();
            });
          });

          function onCropChange() {
            if(autoCrop) {
              scope.resultImage = getDataURL();
              scope.$apply();
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
