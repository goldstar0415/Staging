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
        items: '@',
        index: '@',
        currentItem: '@'
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
            },
            photoData: function() {
              return Photo.get({id: s.currentItem.id});
            }
          }
        });
      });
    }

    function PhotoViewerController($modalInstance, items, index, photoData, Photo) {
      var vm = this;
      var currentIndex = index;
      vm.currentPhoto = photoData;

      vm.nextPhoto = function () {
        var nextIndex;
        if(currentIndex + 1 > items.length - 1) {
          nextIndex = 0;
        } else {
          nextIndex = currentIndex + 1;
        }

        vm.currentPhoto = Photo.get({id: items[nextIndex].id});
        currentIndex = nextIndex;
      };
      vm.previousPhoto = function() {
        var prevIndex;
        if(currentIndex - 1 < 0) {
          prevIndex = items.length - 1;
        } else {
          prevIndex = currentIndex - 1;
        }

        vm.currentPhoto = Photo.get({id: items[prevIndex].id});
        currentIndex = prevIndex;
      };
      vm.sendComment = function() {
        var id  = vm.currentPhoto.id;
        Photo.postComment({id: id});
      };
      vm.deleteComment = function(commentId) {
        var id  = vm.currentPhoto.id;
        Photo.deleteComment({id: id,comment_id: commentId});
      };
      vm.fullScreen  = function() {

      };
    }
  }
})();
