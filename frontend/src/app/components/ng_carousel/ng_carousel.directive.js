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

      if (vm.images.length == 0) {
        return;
      }

      vm.mainImage = vm.images[0];
      vm.mainImage.idx = 0;
      vm.imageControl = {
        start: 0,
        step: 6
      };

      vm.prev = prev;
      vm.next = next;
      vm.setMainImage = setMainImage;

      function prev() {
        vm.imageControl.start--;
      }

      function next() {
        vm.imageControl.start++;
      }

      function setMainImage(item, idx) {
        vm.mainImage = item;
        vm.mainImage.idx = vm.imageControl.start + idx;
      }
    }
  }

})();
