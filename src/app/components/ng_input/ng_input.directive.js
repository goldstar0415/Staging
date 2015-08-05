(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('ngInput', ngInput);

  /** @ngInject */
  function ngInput() {
    return {
      restrict: 'E',
      scope: {
        message: '=',
        onSubmit: '&',
        onFocus: '&'
      },
      templateUrl: 'app/components/ng_input/ng_input.html',
      controller: NgInputController,
      controllerAs: 'NgInput',
      bindToController: true
    };

    /** @ngInject */
    function NgInputController($modal, $rootScope) {
      var vm = this;
      vm.message.attachments = {};

      vm.submit = function (form) {
        if (form.$valid) {
          vm.onSubmit();
        }
      };

      vm.openPhotosModal = function () {
        $modal.open({
          templateUrl: 'PhotosModal.html',
          controller: PhotosModalController,
          controllerAs: 'modal',
          modalContentClass: 'clearfix',
          resolve: {
            albums: function (Album) {
              return Album.query({user_id: $rootScope.currentUser.id}).$promise;
            },
            attachments: function () {
              return vm.message.attachments;
            }
          }
        });
      };

      vm.openActivityModal = function () {

      }
    }

    function PhotosModalController(Album, albums, attachments, $modalInstance) {
      var vm = this;
      vm.albums = albums;
      vm.attachments = attachments;

      vm.selectAlbum = function (id) {
        vm.selectedAlbum =  Album.get({id: id}, function (album) {

          return album;
        });
      };

      vm.addPhoto = function (idx) {
        var photo = vm.selectedAlbum.photos.splice(idx, 1);
        vm.attachments.images = vm.attachments.images || [];
        vm.attachments.images.push(photo);
      };
    }

  }

})();
