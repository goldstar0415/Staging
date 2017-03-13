(function () {
  'use strict';

  /*
   * Popular posts block
   */
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
    function PopularPostsController(Post, $scope) {
      var vm = this;
      vm.popular_category = '';

      //load categories
      Post.categories().$promise.then(function (catergories) {
        catergories.unshift({id: '', name: "all", display_name: "All"});
        vm.categories = catergories;
      });

      //load new posts on selected category
      $scope.$watch('Post.popular_category', function (val) {
        vm.posts = Post.popular({category: val});
      });

    }

  }

})();
