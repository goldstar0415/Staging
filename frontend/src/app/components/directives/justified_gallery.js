(function () {
  'use strict';

  /*
   * Directive to formatted attached images.
   * Use "justifiedGallery" plugin
   */
  angular
    .module('zoomtivity')
    .directive('justifiedGallery', function ($timeout, $ocLazyLoad) {
      return {
        restrict: 'A',
        link: function (s, e, a) {

          s.$watch('$last', function (n, o) {
            if (n) {
              $timeout(function () {
                lazyJg();
              })
            }
          });

          function lazyJg() {
            if ($ocLazyLoad.isLoaded('justifiedGallery')) {
              jg();
            } else {
              $ocLazyLoad.load('justifiedGallery').then(jg);
            }
          }

          function jg() {
            e.justifiedGallery({
              //rowHeight: 100,
              //maxRowHeight: 150,
              //lastRow: 'justify',
              margins: 1,
              captions: false,
              cssAnimation: true
              //randomize: true
            });
          }
        }
      }
    })
})();
