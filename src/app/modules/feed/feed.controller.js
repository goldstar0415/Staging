(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FeedsController', FeedsController);

  /** @ngInject */
  function FeedsController(feeds) {
    var vm = this;
    vm.limit = 10;
    vm.page = 1;
    vm.feeds = feeds;


  }
})();
