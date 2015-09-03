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
        hideComments: '@'
      },
      link: PhotoViewerLink
    };

    function PhotoViewerLink(s, e, a) {
      $(e).on('click', function () {
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
            hideComments: function () {
              return !!s.hideComments;
            }
          }
        });
      });
    }

    /** @ngInject */
    function PhotoViewerController($modalInstance, items, index, hideComments, Photo) {
      var vm = this;
      vm.countPhotos = items.length;
      vm.hideComments = hideComments;
      vm.currentIndex = index;
      vm.currentPhoto = items[index];
      vm.comments = getComments(items[index].id);

      vm.nextPhoto = function nextPhoto() {
        var nextIndex;
        if (vm.currentIndex + 1 > items.length - 1) {
          nextIndex = 0;
        } else {
          nextIndex = vm.currentIndex + 1;
        }

        vm.currentPhoto = items[nextIndex];//Photo.get({id: items[nextIndex].id});
        vm.comments = getComments(items[nextIndex].id);
        vm.currentIndex = nextIndex;
      };
      vm.previousPhoto = function () {
        var prevIndex;
        if (vm.currentIndex - 1 < 0) {
          prevIndex = items.length - 1;
        } else {
          prevIndex = vm.currentIndex - 1;
        }

        vm.currentPhoto = items[prevIndex];//Photo.get({id: items[prevIndex].id});
        vm.comments = getComments(items[prevIndex].id);

        vm.currentIndex = prevIndex;
      };
      vm.sendComment = function (form) {
        if (form.$valid) {
          var id = vm.currentPhoto.id;
          Photo.save({id: id}, {body: vm.comment}, function (comment) {
            vm.comments.unshift(comment);
            vm.comment = '';
          });
        }
      };
      vm.deleteComment = function (commentId) {
        var id = vm.currentPhoto.id;
        Photo.deleteComment({id: id, comment_id: commentId});
      };

      function getComments(id) {
        if (_.isUndefined(hideComments) || !hideComments) {
          return Photo.getComments({id: id});
        }
      }
    }
  }
})();
