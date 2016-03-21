(function () {
  'use strict';

  /*
   * Ng carousel
   */
  angular
    .module('zoomtivity')
    .directive('ngCarousel', ngCarousel);

  /** @ngInject */
  function ngCarousel() {
    return {
      restrict: 'EA',
      templateUrl: '/app/components/ng_carousel/ng_carousel.html',
      scope: {
        images: '='
      },
      controller: NgCarouselController,
      controllerAs: 'Carousel',
      bindToController: true
    };

    /** @ngInject */
    function NgCarouselController() {
      var vm = this;

      vm.mainImage = vm.images[0];
      vm.imageControl = {
        start: 0,
        step: 4
      };

      vm.prev = prev;

      function prev() {
        vm.imageControl.start--;
      }

      vm.next = next;

      function next() {
        vm.imageControl.start++;
      }
    }
  }

})();
