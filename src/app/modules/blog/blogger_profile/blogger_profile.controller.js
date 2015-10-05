(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('BloggerProfileController', BloggerProfileController);

  /** @ngInject */
  function BloggerProfileController(posts, Post, dialogs, MapService) {
    var vm = this;
    vm.removePost = removePost;
    vm.posts = posts;

    showMarkers();


    /*
     * Delete post
     * @param post {Post}
     * @param idx {number} post index
     */
    function removePost(post, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete post?').result.then(function () {
        Post.delete({id: post.id}, function () {
          toastr.info('Spot successfully deleted');
          vm.posts.data.splice(idx, 1);
        });
      });
    }

    //Show markers on map
    function showMarkers() {
      if (posts.length > 0) {
        MapService.drawBlogMarkers(posts, true);
      }
    }

  }
})();
