(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ReviewsController', ReviewsController);

  /** @ngInject */
  function ReviewsController(reviews) {
    var vm = this;
    vm.limit = 10;
    vm.page = 1;
    vm.reviews = reviews;


  }
})();
