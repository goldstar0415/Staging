(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('BlogggerProfileController', BlogggerProfileController);

  /** @ngInject */
  function BlogggerProfileController(posts, Post, dialogs, MapService) {
    var vm = this;
    vm.removePost = removePost;
    vm.posts = posts;

    showMarkers();

    function removePost(post, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete post?').result.then(function () {
        Post.delete({id: post.id}, function () {
          toastr.info('Spot successfully deleted');
          vm.posts.data.splice(idx, 1);
          //if (vm.markersSpots[idx].marker) {
          //  console.log('single marker', vm.markersSpots[idx].marker);
          //  MapService.GetCurrentLayer().removeLayer(vm.markersSpots[idx].marker);
          //} else {
          //  console.log('Multiple markers');
          //  MapService.GetCurrentLayer().removeLayers(vm.markersSpots[idx].markers)
          //}
        });
      });
    }

    function showMarkers() {
      MapService.drawBlogMarkers(posts, true);
    }

  }
})();
