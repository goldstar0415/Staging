(function () {
  'use strict';

  /*
   * Photo viewer modal
   */
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
        nocoments: '='
      },
      link: PhotoViewerLink
    };

    function PhotoViewerLink(s, e, a) {
      if(e[0].classList.contains('spot-album-photo'))
      {
        e.bind('error', function(){
            $(e).parent().remove();
        });
      }
      
      $(e).on('click', function () {
        //   debugger;
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
            nocoments: function () {
              return !!s.nocoments;
            }
          }
        });
      });
    }

    /** @ngInject */
    function PhotoViewerController($scope, $document, $modalInstance, items, index, nocoments, PhotoComment, SpotPhotoComments) {
      var vm = this;
      vm.items = items;
      $scope.index = index;
      vm.nocoments = nocoments;
      vm.countPhotos = items.length;
      vm.displayComments = true;
      if (nocoments) {
          vm.displayComments = false;
      }
      setPhoto(index);
      $document.find('html').addClass('modal-opened');

      vm.toggleComments = function() {
          vm.displayComments = !vm.displayComments;
      }

      $scope.$watch('index', function() {
          setPhoto($scope.index);
      });

      /*
       * Send comment to photo
       * @param form {ngForm}
       */
      vm.sendComment = function (form) {
        if (form.$valid) {
          if (vm.currentPhoto.album_id) {
            PhotoComment.save({photo_id: vm.currentPhoto.id}, {body: vm.comment}, afterSave);
          } else {
            SpotPhotoComments.save({
              photo_id: vm.currentPhoto.id,
              spot_id: vm.currentPhoto.spot_id
            }, {body: vm.comment}, afterSave);
          }
        }
      };

      /*
       * Delete photo comment
       * @param commentId {number} comment id
       * @param idx {number} comment index
       */
      vm.deleteComment = function (commentId, idx) {
        if (vm.currentPhoto.album_id) {
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

      //close modal
      vm.close = function () {
        $document.find('html').removeClass('modal-opened');
        $modalInstance.close();
      };

      /*
       * Set current photo by index
       * @param idx {number} comment index
       */
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

      /*
       * Load comments of photo
       * @param item {Photo}
       */
      function getComments(item) {
        if (_.isUndefined(nocoments) || !nocoments) {
          return item.album_id ?
            PhotoComment.query({photo_id: item.id}) :
            SpotPhotoComments.query({photo_id: item.id, spot_id: item.spot_id})
        }
      }
    }
  }
})();
