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
    function PhotoViewerController($modalInstance, items, index, hideComments, PhotoComment) {
      var vm = this;
      vm.countPhotos = items.length;
      vm.hideComments = hideComments;
      setPhoto(index);

      vm.nextPhoto = function nextPhoto() {
        setPhoto(vm.currentIndex + 1);
      };
      vm.previousPhoto = function () {
        setPhoto(vm.currentIndex - 1);
      };
      vm.sendComment = function (form) {
        if (form.$valid) {
          var id = vm.currentPhoto.id;
          PhotoComment.save({photo_id: id}, {body: vm.comment}, function (comment) {
            vm.comments.unshift(comment);
            vm.comment = '';
          });
        }
      };
      vm.deleteComment = function (commentId, idx) {
        var id = vm.currentPhoto.id;
        PhotoComment.delete({photo_id: id, id: commentId}, function () {
          vm.comments.splice(idx, 1);
        });
      };

      function setPhoto(idx) {
        if (idx > items.length - 1) {
          idx = 0;
        } else if (idx < 0) {
          idx = items.length - 1;
        }

        vm.currentPhoto = items[idx];
        vm.comments = getComments(items[idx].id);
        vm.currentIndex = idx;
      }

      function getComments(id) {
        if (_.isUndefined(hideComments) || !hideComments) {
          return PhotoComment.query({photo_id: id});
        }
      }
    }
  }
})();
