(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('BlogController', BlogController);

  /** @ngInject */
  function BlogController($stateParams, Post, ScrollService) {
    var vm = this;

    vm.posts = {};
    var params = {
      page: 0,
      limit: 10,
      user_id: vm.isPersonalBlog ? $stateParams.user_id : undefined
    };
    vm.pagination = new ScrollService(Post.paginate, vm.posts, params);

    vm.getDate = getDate;

    ////////////////////////////

    function getDate(date) {
      var $date = moment(date);
      return $date.format('DD') + '<br/>' + $date.format('MMM');
    }
  }
})();
