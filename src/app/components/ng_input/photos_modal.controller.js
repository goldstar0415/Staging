(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PhotosModalController', PhotosModalController);

  /** @ngInject */
  function PhotosModalController(Album, albums, attachments, $modalInstance) {
    var vm = this;
    vm.albums = albums;
    vm.attachments = attachments;

    vm.selectAlbum = function (id, idx) {
      vm.selectedAlbum = vm.albums[idx];
      Album.photos({album_id: id}, function (photos) {
        vm.selectedAlbum.photos = _.filter(photos, function (photo) {
          return !_.findWhere(vm.attachments.photos, {id: photo.id});
        });
      });
    };

    vm.addPhoto = function (idx) {
      var photo = vm.selectedAlbum.photos.splice(idx, 1);
      vm.attachments.photos.push(photo[0]);
    };

    vm.close = function () {
      $modalInstance.close();
    };
  }
})();
