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
    function PhotoViewerController($modalInstance, items, index, hideComments, PhotoComment, SpotPhotoComments) {
      var vm = this;
      var isAlbumComments = angular.isDefined(items[0].album_id);
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
          if (isAlbumComments) {
            PhotoComment.save({photo_id: vm.currentPhoto.id}, {body: vm.comment}, afterSave);
          } else {
            SpotPhotoComments.save({
              photo_id: vm.currentPhoto.id,
              spot_id: vm.currentPhoto.spot_id
            }, {body: vm.comment}, afterSave);
          }
        }
      };
      vm.deleteComment = function (commentId, idx) {
        if (isAlbumComments) {
          PhotoComment.delete({
            photo_id: vm.currentPhoto.id,
            id: commentId
          }, afterDelete);
        } else {
          SpotPhotoComments.delete({
            photo_id: vm.currentPhoto.id,
            spot_id: vm.currentPhoto.spot_id,
            id: commentId
          }, afterDelete);
        }

        function afterDelete() {
          vm.comments.splice(idx, 1);
        }
      };

      function setPhoto(idx) {
        if (idx > items.length - 1) {
          idx = 0;
        } else if (idx < 0) {
          idx = items.length - 1;
        }

        vm.currentPhoto = items[idx];
        vm.comments = getComments(items[idx]);
        vm.currentIndex = idx;
      }

      function afterSave(comment) {
        vm.comments.unshift(comment);
        vm.comment = '';
      }

      function getComments(item) {
        if (_.isUndefined(hideComments) || !hideComments) {
          return isAlbumComments ?
            PhotoComment.query({photo_id: item.id}) :
            SpotPhotoComments.query({photo_id: item.id, spot_id: item.spot_id})
        }
      }
    }
  }
})();
