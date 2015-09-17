(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ArticleController', ArticleController);

  /** @ngInject */
  function ArticleController(article, ScrollService, PostComment, dialogs, toastr) {
    var vm = this;
    vm = _.extend(vm, article);
    vm.sendComment = sendComment;
    vm.deleteComment = deleteComment;

    vm.comments = {};
    var params = {
      page: 0,
      limit: 10,
      post_id: article.id
    };
    vm.pagination = new ScrollService(PostComment.query, vm.comments, params);

    function sendComment(form) {
      if (form.$valid) {
        PostComment.save({post_id: article.id},
          {
            body: vm.message
          }, function success(message) {
            vm.comments.data.unshift(message);
            vm.message = '';
          }, function error(resp) {
            toastr.error('Send message failed');
          });
      }
    }

    function deleteComment(comment, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete comment?').result.then(function () {
        PostComment.delete({post_id: article.id, id: comment.id}, function () {
          toastr.info('Comment successfully deleted');
          vm.comments.data.splice(idx, 1);
        });
      });
    }

  }
})();
