(function () {
  'use strict';

  angular.module('zoomtivity.filters', []).
  filter('htmlToPlaintext', function() {
    return function(text) {
      return  text ? String(text).replace(/<[^>]+>/gm, '') : '';
    };
  }
);

  angular
    .module('zoomtivity')
    .controller('BlogController', BlogController);

  /** @ngInject */
  function BlogController($stateParams, Post, ScrollService, Share, toastr, dialogs) {
    var vm = this;

    vm.posts = {};
    var params = {
      page: 0,
      limit: 10,
      user_id: vm.isPersonalBlog ? $stateParams.user_id : undefined
    };
    vm.pagination = new ScrollService(Post.paginate, vm.posts, params);

    console.log(vm.posts);

    vm.getDate = getDate;
    vm.sharePost = sharePost;
    vm.removePost = removePost;

    ////////////////////////////

    function getDate(date) {
      var $date = moment(date);
      return $date.format('DD') + '<br/>' + $date.format('MMM');
    }

    function sharePost(post) {
      Share.openModal(post, 'post');
    }

    function removePost(post, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete post?').result.then(function () {
        Post.delete({id: post.slug || post.id},
            function () {
              toastr.info('Spot successfully deleted');
              vm.posts.data.splice(idx, 1);
            },
            function () {
              toastr.error('An error occurred during deleting');
            }
        );
      });
    }

  }
})();
