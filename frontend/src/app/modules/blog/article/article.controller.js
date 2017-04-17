(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ArticleController', ArticleController);

  /** @ngInject */
  function ArticleController(article, ScrollService, PostComment, dialogs, toastr, $window, Share, Post) {
    var vm = this;
    vm = _.extend(vm, article);
    vm.sendComment = sendComment;
    vm.deleteComment = deleteComment;
    vm.getDate = getDate;
    vm.back = back;
    vm.sharePost = sharePost;
    vm.removePost = removePost;

    vm.comments = {};
    var params = {
      page: 0,
      limit: 10,
      post_id: article.slug || article.id
    };
    vm.pagination = new ScrollService(PostComment.query, vm.comments, params);

    function getDate(date) {
      var $date = moment(date);
      return $date.format('DD') + '<br/>' + $date.format('MMM');
    }

    function back() {
        $window.history.back();
    }

    /*
     * Send comment to form
     * @param form {ngForm}
     */
    function sendComment() {
      PostComment.save({post_id: article.slug || article.id},
        {
          body: vm.message || '',
          attachments: {
            album_photos: _.pluck(vm.attachments.photos, 'id'),
            spots: _.pluck(vm.attachments.spots, 'id'),
            areas: _.pluck(vm.attachments.areas, 'id'),
            links: vm.attachments.links
          }
        }, function success(message) {
          vm.comments.data.unshift(message);
          vm.message = '';
          vm.attachments.photos = [];
          vm.attachments.spots = [];
          vm.attachments.areas = [];
          vm.attachments.links = [];
        }, function error(resp) {
          toastr.error('Send message failed');
        });
    }

    /*
     * Delete comment
     * @param comment {PostComment}
     * @param idx {number} comment index
     */
    function deleteComment(comment, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete comment?').result.then(function () {
        PostComment.delete({post_id: article.slug || article.id, id: comment.id}, function () {
          toastr.info('Comment successfully deleted');
          vm.comments.data.splice(idx, 1);
        });
      });
    }

    function sharePost() {
      Share.openModal(vm, 'post');
    }

    function removePost() {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete post?').result.then(function () {
        Post.delete({id: vm.slug || vm.id},
            function () {
              toastr.info('Spot successfully deleted');
              back();
            },
            function () {
              toastr.error('An error occurred during deleting');
              back();
            }
        );
      });
    }

  }
})();
