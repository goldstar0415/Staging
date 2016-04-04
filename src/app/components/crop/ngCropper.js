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
          var MAX_WIDTH = 1024;

          var cropOption = {
            aspectRatio: ratioWidth / ratioHeight,
            cropmove: onCropChange,
            cropend: onCropEnd,
            autoCrop: true,
            strict: true
          };

          $image.on('built.cropper', onCropEnd);


          //crop image after it to be loaded

          scope.$watch('sourceImage', function (newValue, oldValue) {
            if (newValue) {
              var img = new Image;
              img.src = newValue;

              img.onload = function () {
                if (img.width > MAX_WIDTH) {
                  var canvas = document.createElement("canvas"),
                  ctx = canvas.getContext("2d");

                  canvas.width = img.width / (img.width / MAX_WIDTH);
                  canvas.height = img.height / (img.width / MAX_WIDTH);

                  ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                  setImage(canvas.toDataURL('image/png'));
                } else {
                  setImage(img.src);
                }
              };
            }
          });

          function setImage(base64) {
            if ($image.attr('src')) {
              $image.cropper('replace', base64);
            } else {
              $image
                .attr('src', base64)
                .cropper(cropOption);
            }
          }

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
            return $image.cropper('getCroppedCanvas').toDataURL('image/png');
          }
        }
      }
    });
})();
