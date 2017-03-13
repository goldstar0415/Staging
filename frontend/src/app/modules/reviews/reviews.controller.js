(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ReviewsController', ReviewsController);

  /** @ngInject */
  function ReviewsController(Feed, ScrollService) {
    var vm = this;
    vm.limit = 10;
    vm.page = 1;
    vm.reviews = {};

    var params = {
      page: 0,
      limit: 10
    };
    vm.pagination = new ScrollService(Feed.reviews, vm.reviews, params);

  }
})();
