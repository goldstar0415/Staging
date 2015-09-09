(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('BlogController', BlogController);

  /** @ngInject */
  function BlogController($stateParams, Post, dialogs, ScrollService) {
    var vm = this;
    vm.isPersonalBlog = !!$stateParams.user_id;
    vm.removePost = removePost;

    vm.posts = {};
    var params = {
      page: 0,
      limit: 10,
      user_id: vm.isPersonalBlog ? $stateParams.user_id : undefined
    };
    vm.pagination = new ScrollService(Post.query, vm.posts, params);


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

  }
})();
