(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('imageCropper', function ($timeout, $rootScope) {
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

          scope.$watch('resultImage', function (v, o) {
            //console.log(v, o);
          });

          $image.one('load', function () {
            console.log(111);
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
            $timeout(function () {
              scope.resultImage = getDataURL();
            });

          }).each(function () {
            if (this.complete) $(this).load();
          });

          function onCropChange() {
            if (autoCrop) {
              onCropEnd();
            }
          }

          function onCropEnd() {
            scope.resultImage = getDataURL();
            console.log('onCropEnd', scope.resultImage);
            scope.$apply();
          }

          function getDataURL() {
            return $image.cropper('getCroppedCanvas').toDataURL()
          }
        }
      }
    });
})();
