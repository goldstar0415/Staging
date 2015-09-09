(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('popularPosts', popularPosts);

  /** @ngInject */
  function popularPosts() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/popular_posts/popular_posts.html',
      scope: {
        showBanner: '='
      },
      controller: PopularPostsController,
      controllerAs: 'Post',
      bindToController: true
    };

    /** @ngInject */
    function PopularPostsController(Post) {
      var vm = this;
      vm.posts = Post.popular();
      vm.categories = Post.categories();


    }

  }

})();
