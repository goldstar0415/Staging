(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('BlogController', BlogController);

  /** @ngInject */
  function BlogController(popularPosts, categories, $stateParams, Post, ScrollService) {
    var vm = this;
    vm.isPersonalBlog = !!$stateParams.user_id;
    vm.categories = categories;
    vm.popularPosts = popularPosts;

    vm.posts = {};
    var params = {
      page: 0,
      limit: 10,
      user_id: vm.isPersonalBlog ? $stateParams.user_id : undefined
    };
    vm.pagination = new ScrollService(Post.query, vm.posts, params);

  }
})();
