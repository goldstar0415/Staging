(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FeedsController', FeedsController);

  /** @ngInject */
  function FeedsController(Feed, ScrollService) {
    var vm = this;
    vm.feeds = {};

    var params = {
      page: 0,
      limit: 10
    };
    vm.pagination = new ScrollService(Feed.query, vm.feeds, params);
  }
})();
