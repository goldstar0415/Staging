(function () {
  'use strict';

  /*
   * Directive to formatted attached images.
   * Use "justifiedGallery" plugin
   */
  angular
    .module('zoomtivity')
    .directive('justifiedGallery', function ($timeout) {
      return {
        restrict: 'A',
        link: function (s, e, a) {
          s.$watch('$last', function (n, o) {
            if (n) {
              $timeout(function () {
                e.justifiedGallery({
                  rowHeight: 100,
                  maxRowHeight: 150,
                  lastRow: 'justify',
                  margins: 1,
                  captions: false,
                  cssAnimation: true
                  //randomize: true
                });
              })
            }
          });
        }
      }
    })
})();
