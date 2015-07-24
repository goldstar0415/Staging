(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('photoViewer', photoViewer);

  /** @ngInject */
  function photoViewer($modal, Photo) {
    return {
      restrict: 'A',
      scope: {
        items: '=',
        index: '=',
        currentItem: '='
      },
      link: PhotoViewerLink
    };

    function PhotoViewerLink(s, e, a) {
      $(e).on('click', function() {
        $modal.open({
          animation: true,
          templateUrl: 'photoViewer.html',
          controller: PhotoViewerController,
          controllerAs: 'PV',
          modalClass: 'viewer-wrap',
          resolve: {
            items: function () {
              return s.items;
            },
            index: function () {
              return s.index;
            }
          }
        });
      });
    }

    function PhotoViewerController($modalInstance, items, index, Photo) {
      var vm = this;
      vm.currentIndex = index;
      vm.currentPhoto = items[index];
      vm.comments = Photo.getComments({id: items[index].id});
      vm.nextPhoto = function (e) {
        e.preventDefault();
        var nextIndex;
        if(vm.currentIndex + 1 > items.length - 1) {
          nextIndex = 0;
        } else {
          nextIndex = vm.currentIndex + 1;
        }

        vm.currentPhoto = items[nextIndex];//Photo.get({id: items[nextIndex].id});
        vm.comments = Photo.getComments({id: items[nextIndex].id});
        vm.currentIndex = nextIndex;
      };
      vm.previousPhoto = function(e) {
        e.preventDefault();
        var prevIndex;
        if(vm.currentIndex - 1 < 0) {
          prevIndex = items.length - 1;
        } else {
          prevIndex = vm.currentIndex - 1;
        }

        vm.currentPhoto = items[prevIndex]//Photo.get({id: items[prevIndex].id});
        vm.comments = Photo.getComments({id: items[prevIndex].id});
        vm.currentIndex = prevIndex;
      };
      vm.sendComment = function() {
        var id  = vm.currentPhoto.id;
        Photo.postComment({id: id});
      };
      vm.deleteComment = function(commentId) {
        var id  = vm.currentPhoto.id;
        Photo.deleteComment({id: id,comment_id: commentId});
      };
    }
  }
})();
