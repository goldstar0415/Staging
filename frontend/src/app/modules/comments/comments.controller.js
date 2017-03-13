(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('CommentsController', CommentsController);

  /** @ngInject */
  function CommentsController(Feed, ScrollService) {
    var vm = this;
    vm.limit = 10;
    vm.page = 1;
    vm.comments = {};

    var params = {
      page: 0,
      limit: 10
    };
    vm.pagination = new ScrollService(Feed.comments, vm.comments, params);

  }
})();
